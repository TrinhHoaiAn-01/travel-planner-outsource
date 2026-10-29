# AI Chatbot Discord --- Detailed Pipeline Specification

> Mục đích: tài liệu này mô tả pipeline theo từng bước, đầu vào, xử lý,
> đầu ra, điều kiện rẽ nhánh và trách nhiệm từng module. AI coding agent
> cần đọc tài liệu này cùng source code hiện tại trước khi thay đổi hệ
> thống.
>
> **Quy ước trạng thái** - **Đã có theo code đã trao đổi**: hành vi đã
> xuất hiện trong các đoạn code được xem xét. - **Cần xác minh**: phụ
> thuộc vào code/repository thực tế hoặc cấu hình runtime. - **Đề xuất
> phát triển**: mục tiêu tương lai, không được giả định đã triển khai.

------------------------------------------------------------------------

## 1. Mục tiêu hệ thống

Xây dựng chatbot Discord cho Travel Planner. Chatbot nhận câu hỏi bằng
ngôn ngữ tự nhiên, truy xuất dữ liệu của hệ thống qua RAG, dùng lịch sử
hội thoại để hiểu câu hỏi tiếp nối, gọi LLM để tạo câu trả lời và lưu
lịch sử vào MySQL.

Trách nhiệm chính: - **Discord + `main.py`**: nhận message, kiểm tra
điều kiện phản hồi, điều phối các service. - **`MySQLService`**: quản lý
session, lưu message, đọc lịch sử và lưu metadata tệp. -
**`RAGService`**: điều phối domain classification, retrieval và build
context. - **Query understanding**: phân loại domain; intent là phần cần
phát triển riêng. - **Entity modules**: nhận diện entity, phân giải
entity và lọc tài liệu. - **Retrieval modules**: BM25 và semantic
search, sau đó kết hợp kết quả. - **`AIService`**: chọn provider, tạo
messages và gọi LLM. - **Data scripts**: chuyển SQL thành documents, tạo
embeddings và xây FAISS index.

## 2. Pipeline tổng thể khi người dùng gửi message

``` mermaid
flowchart TD
    A[Discord message] --> B{Có cần phản hồi không?}
    B -- Không --> Z[Bỏ qua message]
    B -- Có --> C[Loại mention và chuẩn hóa query]
    C --> D{Query rỗng?}
    D -- Có --> E[Gửi yêu cầu nhập câu hỏi]
    D -- Không --> F[Lấy hoặc tạo session MySQL]
    F --> G{Session hợp lệ?}
    G -- Không --> H[Báo lỗi lịch sử]
    G -- Có --> I[Lưu user message]
    I --> J{Lưu thành công?}
    J -- Không --> K[Báo lỗi lưu câu hỏi]
    J -- Có --> L[Lấy chat history]
    L --> M[Loại câu hỏi hiện tại khỏi history]
    M --> N[RAGService.hybrid_search]
    N --> O[Domain classification]
    O --> P[Entity extraction / resolution]
    P --> Q[Candidate document filtering]
    Q --> R[BM25 retrieval]
    Q --> S[Semantic retrieval]
    R --> T[Combine and deduplicate]
    S --> T
    T --> U[Build context]
    U --> V[AIService.ask]
    V --> W{LLM thành công?}
    W -- Không --> X[Báo lỗi LLM]
    W -- Có --> Y[Lưu assistant message]
    Y --> AA[Gửi câu trả lời Discord]
```

**Lưu ý:** sơ đồ mô tả các bước trong code đã trao đổi. Intent
Classification và xử lý nội dung media chưa được tích hợp thành bước
riêng trong pipeline hiện tại.

### 2.1. Bước nhận message --- `main.py`

**Đầu vào:** Discord message, thông tin
author/channel/mentions/attachments.

**Xử lý:** 1. Bỏ qua message nếu `message.author.bot` là `True`. 2. Phản
hồi nếu message nằm trong `DISCORD_CHANNEL_ID`, là DM, hoặc mention bot.
3. Nếu không thỏa điều kiện, return. 4. Ghi message ra console. 5. Loại
`<@BOT_ID>` và `<@!BOT_ID>` khỏi nội dung. 6. Trim query. 7. Nếu query
rỗng, gửi "Bạn muốn tìm kiếm thông tin gì?" và dừng.

**Đầu ra:** query đã loại mention.

**Điểm cần nhớ:** nếu message có attachment nhưng không có text, code
hiện tại có thể xem query là rỗng và dừng. Để hỗ trợ message chỉ có
ảnh/audio/video, cần bổ sung nhánh xử lý attachment trước khi từ chối
query rỗng.

### 2.2. Session và lịch sử --- `MySQLService`

**Đầu vào:** `user_id`, `channel_id`, query.

**Xử lý:** 1. `get_or_create_session(user_id, channel_id)`. 2. Nếu không
lấy/tạo được session, gửi lỗi và dừng. 3.
`save_message(session_id, role="user", content=query)`. 4. Nếu lưu thất
bại, gửi lỗi và dừng. 5. `get_chat_history(session_id, limit=20)`. 6.
Loại message có `message_id` bằng ID câu hỏi vừa lưu.

**Đầu ra:** `session_id`, `user_message_id`, `chat_history` chỉ gồm các
message trước câu hỏi hiện tại.

**Yêu cầu:** history cần ở thứ tự thời gian tăng dần, role phải là
`user` hoặc `assistant` trước khi gửi cho LLM. Cách truy vấn hiện tại
lấy message ID mới nhất rồi đảo danh sách, phù hợp nếu ID phản ánh thứ
tự tạo message.

------------------------------------------------------------------------

## 3. Pipeline chuẩn bị dữ liệu RAG (offline hoặc khi cập nhật dữ liệu)

Pipeline này không chạy lại toàn bộ cho từng câu hỏi. Nó chuẩn bị dữ
liệu mà retrieval dùng lúc runtime.

``` mermaid
flowchart TD
    A[SQL file trong raw/] --> B[Đọc nội dung SQL]
    B --> C[Tìm INSERT statements]
    C --> D[Tách columns, rows, values]
    D --> E[Tạo documents]
    E --> F[data/documents.json]
    F --> G[EmbeddingService cho từng document]
    G --> H[data/embeddings/<model>/documents_embeddings.json]
    H --> I[Chuẩn hóa vector]
    I --> J[Tạo FAISS IndexFlatIP]
    J --> K[data/indexes/<model>/faiss_index.bin]
    J --> L[Metadata và thứ tự tài liệu]
```

### 3.1. SQL → `documents.json`

**Script:** script chuyển SQL sang JSON.

**Đầu vào:** file `.sql` trong `raw/`.

**Xử lý:** 1. Tìm file SQL. 2. Đọc toàn bộ nội dung. 3. Tìm câu lệnh
`INSERT INTO table (columns) VALUES`. 4. Tìm dấu `;` kết thúc câu lệnh
ngoài chuỗi. 5. Tách từng row dựa trên dấu ngoặc. 6. Tách values dựa
trên dấu phẩy ngoài chuỗi. 7. Ghép từng column với value. 8. Tạo
document: - `id`: ví dụ `bookings_12`. - `content`: dictionary chứa dữ
liệu row. - `metadata.type`: tên bảng. - `metadata.source`: nguồn dữ
liệu. - `metadata.table`: tên bảng. - `metadata.row_id`: ID row. 9. Ghi
`data/documents.json`.

**Đầu ra:** danh sách documents có `id`, `content`, `metadata`.

**Giới hạn:** parser dựa trên regex và trạng thái dấu nháy, không phải
parser SQL đầy đủ. Không giả định hỗ trợ mọi kiểu quote, escape,
`INSERT` không có danh sách cột hoặc nhiều dialect.

### 3.2. Documents → embeddings

**Module:** `EmbeddingService`.

**Đầu vào:** `documents.json`.

**Xử lý cho mỗi document:** 1. Lấy `document["content"]`. 2. Chuyển
content thành chuỗi JSON bằng `json.dumps(..., ensure_ascii=False)`. 3.
Gọi `embedding_service.create_document_embedding(content_text)`. E5 dùng
tiền tố `passage:`; BGE-M3 không dùng tiền tố. 4. Chuẩn hóa vector. 5.
Gắn vector vào document. 6. Ghi danh sách vào thư mục riêng theo mô hình
và ghi tên mô hình, số chiều, cách tiền xử lý cùng dấu vân tay vào
`embedding_metadata.json`. Các bảng cá nhân và giao dịch không được tạo
embedding.

**Đầu ra:** documents có thêm embedding vector.

**Quy tắc:** model embedding khi tạo document vector phải giống model
dùng tạo query vector lúc tìm kiếm. Nếu đổi model embedding, cần tái tạo
toàn bộ document embeddings và FAISS index.

### 3.3. Embeddings → FAISS

**Module:** `rag/vectordb/faiss_service.py`.

**Đầu vào:** `data/embeddings/<model>/documents_embeddings.json`.

**Xử lý:** 1. Chọn documents có trường `embedding`. 2. Chuyển danh sách
vectors thành NumPy matrix `float32`. 3. Chuẩn hóa vector bằng
`faiss.normalize_L2`. 4. Tạo `faiss.IndexFlatIP(vector_dimension)`. 5.
Lưu index, danh sách document đúng thứ tự và metadata vào
`data/indexes/<model>/`.

**Khi khởi động `RAGService`:** nạp chỉ mục đã xây dựng trước đó. Nếu
thiếu artifacts hoặc metadata không khớp, dừng với thông báo cần tạo
lại vector/chỉ mục. Việc nạp không tự ghi đè hay âm thầm dùng chỉ mục
384 chiều cũ. Kiểm tra gồm số lượng vector, số chiều, mô hình, cách
tiền xử lý, dấu vân tay nguồn và thứ tự tài liệu.

Khi nội dung nguồn đổi, phải chủ động tạo lại vector và index bằng hai
script đã nêu. Index cũ được giữ lại trong thư mục gốc `data/` để có thể
quay lui.

------------------------------------------------------------------------

## 4. Runtime RAG --- từ query đến documents

``` mermaid
flowchart TD
    A[Query đã chuẩn hóa] --> B[Domain classifier]
    B --> C[Domain label]
    C --> D[Domain document-type filter]
    A --> E[Entity extraction]
    E --> F[Entity resolution]
    F --> G{Entity được phân giải?}
    G -- Có --> H[Lọc documents theo entity]
    G -- Không --> I[Giữ candidate theo domain]
    D --> J[Candidate documents]
    H --> K[Candidate documents]
    I --> K
    J --> L[BM25 search]
    K --> L
    J --> M[Semantic search]
    K --> M
    L --> N[Combine results]
    M --> N
    N --> O[Deduplicate by document ID]
    O --> P[Top-k documents]
    P --> Q[Build context]
```

### 4.1. Domain Classification --- xử lý cụ thể

**Module:** `rag/query_understanding/domain_classifier.py`

**Đầu vào:** query dạng string, ví dụ:

`Tôi muốn xem đơn đặt phòng`

**Quá trình model đã huấn luyện:** 1. `domain_training.json` chứa các ví
dụ query và domain label. 2. `TfidfVectorizer` học vocabulary và biến
query thành vector đặc trưng. 3. `LogisticRegression` học liên hệ giữa
đặc trưng và domain. 4. Model được lưu bằng `joblib`. 5. Khi runtime,
`predict_domain(query)` gọi pipeline đã lưu và đưa dự đoán vào bộ nhớ
đệm. Các request sau dùng cùng pipeline thay vì đọc model từ đĩa lại.

**Đầu ra ví dụ:**

``` text
query = "Tôi muốn xem đơn đặt phòng"
domain = "booking"
```

Đây chỉ là ví dụ minh họa đầu ra mong đợi, không bảo đảm model luôn dự
đoán đúng. Kết quả thực tế phụ thuộc training data và model đã train.

**Sau khi có domain:** `hybrid_retrieve.py` tra
`DOMAIN_DOCUMENT_TYPES`. Các loại dữ liệu cá nhân như `bookings`,
`payments`, `users`, `trips` và `expenses` không được đưa vào retrieval
công khai. Domain không được nhận diện trong bản đồ cũng chỉ tìm trên
những tài liệu công khai.

**Domain không làm gì?** - Không xác định chính xác hành động người dùng
muốn thực hiện. - Không tìm entity cụ thể. - Không tự truy xuất tài
liệu. - Không gọi LLM. - Không đảm bảo candidate documents cuối cùng
liên quan chính xác đến intent.

**Lưu ý huấn luyện:** `train_test_split(..., stratify=domains)` có thể
lỗi nếu số ví dụ của từng lớp quá ít. Cần kiểm tra số mẫu mỗi domain,
đánh giá trên dữ liệu phù hợp và không chỉ dựa vào việc train chạy thành
công.

### 4.2. Intent Classification

Module `rag/query_understanding/intent_classifier.py` đã có model,
training data và hàm dự đoán. Tập huấn luyện có 60 mẫu thuộc 9 intent,
như `search`, `get_details`, `compare`, `check_booking_status`,
`manage_booking`, `payment_query`, `expense_query`, `review_query` và
`general_question`.

`query_router.py` nhận domain và intent để chọn route. `main.py` gọi
router trên câu hỏi độc lập đã dựng từ lịch sử. Route semantic truyền
domain đã chọn xuống bộ lọc trước khi xếp hạng; route đặt phòng, thanh
toán, chi phí và chuyến đi chuyển sang SQL có phân quyền.

Khi câu hiện tại có dấu hiệu là câu nối tiếp, `RAGService` ghép nó với
tối đa hai cặp câu hỏi và câu trả lời gần nhất trong lịch sử, rồi dùng
truy vấn mở rộng để phân loại và tìm tài liệu. Câu hỏi gốc vẫn được giữ
nguyên khi tạo câu trả lời. Câu hỏi độc lập không bị ghép lịch sử; có
thể tắt tính năng bằng `RAG_REWRITE_FOLLOW_UP=false`. Phần câu trả lời
trước được giới hạn 500 ký tự mỗi lượt để giữ truy vấn gọn.

### 4.3. Entity extraction

**Module:** `rag/entity/entity_search.py`.

Ví dụ query:

`Gợi ý địa điểm tham quan ở Đà Nẵng`

Mục tiêu đầu ra:

``` text
recognized entity = "Đà Nẵng"
entity type = location
```

Code đã trao đổi dùng `underthesea.ner` và chỉ thu thập các nhãn
`B-LOC`/`I-LOC`. Vì NER có thể tách tên nhiều từ thành nhiều token, cần
ghép token thành entity hoàn chỉnh và kiểm thử dữ liệu thực tế.

Entity extraction chỉ nhận diện cụm từ; nó chưa khẳng định entity đó tồn
tại trong database.

### 4.4. Entity resolution

**Module:** `rag/entity/entity_resolution.py`.

Đầu vào: entity được trích xuất.

Mục tiêu: 1. Chuẩn hóa entity nếu cần. 2. So khớp entity với dữ liệu hệ
thống. 3. Trả về entity đã phân giải, ví dụ loại `city` và ID thành phố.
4. Nếu không tìm được đối tượng phù hợp, trả trạng thái không phân giải
được.

Ví dụ minh họa:

``` text
"Đà Nẵng"
    → city entity
    → city_id = 2
```

ID ở ví dụ chỉ phản ánh kết quả debug đã trao đổi; không được hard-code
ID này làm quy tắc chung.

### 4.5. Entity filtering

**Module:** `rag/entity/filtered_document_by_entity.py`.

Nếu entity được phân giải: 1. Tìm documents liên quan đến entity. 2. Lấy
tập ID của các documents đó. 3. Giao với candidate documents đã lọc theo
domain. 4. Chỉ giữ documents nằm trong cả hai tập.

Nếu không tìm thấy entity, candidate giữ nguyên khi câu hỏi không yêu
cầu địa điểm; nếu câu hỏi có ngữ cảnh địa điểm nhưng không phân giải
được thì retrieval trả danh sách rỗng. Tên thành phố đối chiếu được
không dấu. Nếu một truy vấn khớp nhiều thành phố, resolver không chọn
một thành phố tùy ý.

### 4.6. BM25 keyword retrieval

**Module:** `rag/retrieve/keyword_retrieve.py`.

**Đầu vào:** query, `top_k`, candidate documents.

**Xử lý:** 1. Tokenize query bằng regex Unicode, giữ nguyên mã có dấu
gạch như `BK-2026-DANANG`. 2. Tạo hoặc dùng BM25
index dựa trên `self.documents`. 3. Tính điểm BM25 cho query. 4. Nếu
candidate documents được cung cấp, chỉ xét documents trong tập đó. 5.
Loại document không chứa bất kỳ token query nào. 6. Thu thập TF, DF, IDF
và BM25 score để debug/result handler. 7. Xếp hạng theo BM25 score giảm
dần. 8. Trả danh sách documents top-k.

**Hệ quả:** query "Tôi muốn xem đơn đặt phòng" có thể không match
booking records nếu nội dung records chỉ chứa field names, IDs, dates,
status và booking codes. Trong debug đã trao đổi, BM25 loại các booking
documents vì không có token query phù hợp.

BM25 không phải semantic search. Không có từ khớp không có nghĩa dữ liệu
chắc chắn không tồn tại.

### 4.7. Semantic retrieval

**Module:** `rag/retrieve/semantic_retrieve.py`.

**Đầu vào:** query và candidate documents.

**Xử lý:** tạo vector truy vấn bằng đúng mô hình và tiền xử lý đã tạo
index. Với candidate filter, lấy vector tài liệu tương ứng và tính
inner product trên vector đã chuẩn hóa. Cách này không dựng index tạm
mỗi lần tìm kiếm. Kết quả có trường `similarity`.

**Điều kiện đúng:** query và document vectors phải cùng embedding
model/dimension; vectors dùng inner product cần được chuẩn hóa để điểm
tương ứng cosine similarity. Thứ tự vector trong FAISS phải khớp với
`faiss_documents`.

### 4.8. Kết hợp hybrid results

**Module:** `rag/retrieve/hybrid_retrieve.py`.

Hiện tại: 1. Lấy danh sách BM25 và semantic. 2. Xếp hạng bằng Reciprocal
Rank Fusion theo vị trí trong từng danh sách. 3. Bỏ mã tài liệu trùng
trong mỗi danh sách để một tài liệu không được cộng điểm nhiều lần từ
cùng một retriever. 4. Trả tối đa `top_k` tài liệu.

Hai loại điểm không được cộng trực tiếp vì có thang đo khác nhau.

### 4.9. Build context

**Module:** `RAGService.build_context()`.

Đầu vào: danh sách documents từ hybrid retrieval.

Xử lý: bỏ trùng mã tài liệu, tối đa 5 tài liệu và 7000 ký tự; giữ mã,
loại, nguồn, metadata và nội dung JSON có Unicode. Nếu document tiếp
theo vượt giới hạn, không thêm một phần nội dung bị cắt dở.

Đầu ra: chuỗi context gửi tới `AIService`.

Context là dữ liệu đầu vào cho LLM; build context không xác minh độ đúng
của documents. Nếu retrieval trả sai tài liệu, context cũng có thể sai.

------------------------------------------------------------------------

## 12. Embedding đa ngôn ngữ và đánh giá

Mô hình mặc định là `intfloat/multilingual-e5-base`. Mô hình này được
chọn làm cấu hình khởi đầu vì nhỏ hơn BGE-M3 và có thể chạy trên CPU.
Máy hiện tại có Intel Core i7-1360P, RAM 15.6 GiB và không phát hiện
`nvidia-smi`. Mô hình chưa được cài cục bộ nên thời gian nạp và RAM khi
chạy E5 chưa đo. Chất lượng trên dữ liệu Travel Planner chưa được đo,
nên chưa thể kết luận mô hình này tốt hơn mô hình cũ. Không yêu cầu GPU.

So sánh tài liệu mô hình: E5 base có 0,3 tỷ tham số và vector 768 chiều;
thẻ mô hình nêu tối đa 512 token và tiền tố `query:`/`passage:` bắt buộc
cho truy xuất. BGE-M3 có vector 1024 chiều, tối đa 8192 token và hỗ trợ
dense, sparse cùng multi-vector. E5 được chọn trước vì footprint tham số
nhỏ hơn và tích hợp dense đơn giản với sentence-transformers hiện có;
BGE-M3 là ứng viên tiếp theo nếu benchmark tiếng Việt cho thấy lợi ích
đáng kể. Đây là lựa chọn theo chi phí triển khai, không phải kết luận
về độ chính xác. Tham khảo [thẻ E5](https://huggingface.co/intfloat/multilingual-e5-base)
và [thẻ BGE-M3](https://huggingface.co/BAAI/bge-m3).

Mô hình không tự tải khi ứng dụng chạy. Tải trước mô hình vào bộ nhớ
đệm Hugging Face bằng lệnh `hf download intfloat/multilingual-e5-base`,
hoặc đặt `EMBEDDING_MODEL` thành đường dẫn thư mục mô hình đã tải. Mặc
định `EMBEDDING_LOCAL_FILES_ONLY=true`.
Chỉ đặt thành `false` nếu muốn cho phép thư viện tự tải mô hình khi
khởi tạo.

E5 nhận tiền tố `query: ` cho câu hỏi và `passage: ` cho tài liệu. Cả
hai phía dùng vector chuẩn hóa và chỉ mục inner product để đo cosine.
Mô hình và chỉ mục nằm trong thư mục riêng theo tên mô hình dưới
`data/embeddings/` và `data/indexes/`; chỉ mục cũ ở `data/faiss_index.bin`
được giữ nguyên. Trên máy phát triển, mô hình E5 đã được tải vào cache
và chỉ mục E5 đã tạo với 410 tài liệu công khai.

Để thử BGE-M3, đặt `EMBEDDING_MODEL=BAAI/bge-m3` và
`EMBEDDING_MODEL_TYPE=bge-m3`, tải mô hình vào cache trước rồi chạy lại
đúng quy trình tạo vector và chỉ mục. Dense embedding của BGE-M3 không
dùng tiền tố E5. Có thể đặt `EMBEDDING_MODEL_TYPE=e5` khi dùng đường dẫn
mô hình E5 cục bộ không chứa tên `intfloat/multilingual-e5`. Đặt
`EMBEDDING_MODEL_TYPE=plain` cho mô hình sentence-transformers không có
tiền tố, chẳng hạn `all-MiniLM-L6-v2`.

Quy trình tạo chỉ mục:

1. Cài các gói trong `requirements/requirements.txt`.
2. Tải mô hình E5 như hướng dẫn ở trên.
3. Chạy `python rag/build/build_embeddings_by_json.py`.
4. Chạy `python rag/vectordb/build_faiss_index.py`.
5. Khởi chạy bot hoặc chạy `python rag/evaluate_retrieval.py`.

Tệp `embedding_metadata.json` lưu tên mô hình, số chiều, cách tiền xử
lý và dấu vân tay tài liệu công khai. Ứng dụng từ chối nạp chỉ mục cũ,
sai số chiều, sai mô hình hoặc khác nội dung nguồn. Tạo vector và chỉ
mục vào thư mục riêng giúp giữ lại bản cũ để quay lui. Để quay lui về
MiniLM, đặt `EMBEDDING_MODEL=all-MiniLM-L6-v2` và
`EMBEDDING_MODEL_TYPE=plain`, sau đó tạo artifacts vào thư mục riêng
bằng hai script ở trên. Chỉ mục cũ ở thư mục gốc được giữ nguyên để so
sánh; runtime mới yêu cầu metadata và sẽ không nạp trực tiếp chỉ mục cũ.

Bộ câu hỏi tại `data/retrieval_evaluation.json` chứa câu hỏi thực tế,
câu diễn đạt lại, tên địa điểm không dấu, mã định danh, câu hỏi nối
tiếp, câu hỏi mơ hồ và trường hợp không có tài liệu. Mỗi câu có danh
sách mã tài liệu liên quan được ghi rõ. Chạy bộ đo bằng:

```powershell
python rag/evaluate_retrieval.py --variant current --follow-up-rewrite off --label e5_hybrid
python rag/evaluate_retrieval.py --variant current --follow-up-rewrite on --label e5_with_followup
```

Kết quả ghi Recall@5, Recall@10, MRR, Precision@5, độ trễ trung bình,
mức dùng bộ nhớ nếu có `psutil`, và số câu không có tài liệu nhưng vẫn
trả kết quả. Các câu không có tài liệu được báo riêng, không tính vào
các chỉ số xếp hạng. So sánh các mô hình phải dùng cùng bộ câu hỏi,
cùng phiên bản tài liệu và cùng cấu hình top-k.

### Kết quả đo hiện tại

Baseline MiniLM và hai cấu hình E5 đã đo; bảng đầy đủ, giới hạn của tập
đánh giá và các tệp JSON nằm trong
`requirements/retrieval_benchmark_report.md`. E5 đạt Recall@5 0.767 khi
tắt viết lại câu nối tiếp và 0.800 khi bật; đây là kết quả trên 12 câu,
trong đó chỉ một câu kiểm tra viết lại. Chưa benchmark BGE-M3 hoặc độ
chính xác câu trả lời của Qwen. Không suy rộng kết quả này thành khẳng
định chất lượng trên toàn bộ truy vấn thực tế.

### Giới hạn và bảo mật

BM25 tiếp tục dùng regex, đồng thời giữ nguyên mã dạng
`BK-2026-DANANG`. Chưa thêm bộ tách từ tiếng Việt hoặc reranker vì chưa
có bằng chứng benchmark để biện minh. Tìm tên thành phố không dấu chỉ
dùng đối chiếu tên thành phố đã có trong dữ liệu; đây không phải mô hình
nhận diện địa danh tổng quát.

Các bảng `users`, `bookings`, `payments`, `expenses`, `trips`,
`itinerary_items` và `favorites` bị loại khỏi chỉ mục RAG công khai.
Các câu hỏi giao dịch được định tuyến tới truy vấn SQL có tham số và
lọc theo `travel_user_id` lấy từ liên kết Discord đang hoạt động. Mã
người dùng viết trong câu hỏi không được dùng để cấp quyền. Việc tạo,
sửa hoặc hủy giao dịch hiện chưa được hỗ trợ.

Yêu cầu về hồ sơ, thông tin nhận dạng hoặc danh sách khách hàng bị từ
chối trước khi lưu câu hỏi, ghi nội dung vào log, gọi SQL, RAG hoặc LLM.
Các loại tài liệu `customers`, `customer_profiles`, `clients` và
`client_profiles` cũng bị loại khỏi embedding và chỉ mục FAISS.

Để bật tra cứu riêng tư, quản trị viên cần chạy thủ công migration
`requirements/discord_user_links.sql`, xác minh danh tính người dùng
bằng quy trình bên ngoài bot, rồi thêm liên kết đã xác minh với
`is_active = 1`. Liên kết mới mặc định bị tắt; đặt `is_active = 0` để
thu hồi. Migration không tự chạy và không có liên kết mặc định trong
cơ sở dữ liệu hiện tại. Nếu chưa có liên kết, bot từ chối tra cứu.

## 5. Từ context đến câu trả lời LLM

### 5.1. `AIService.build_messages()`

Đầu vào: - `prompt`: câu hỏi hiện tại. - `context`: kết quả build
context. - `chat_history`: lịch sử hội thoại trước đó.

Xử lý: 1. Thêm system prompt. 2. Thêm các message history có role `user`
hoặc `assistant`. 3. Tạo message user cuối cùng chứa context nếu có, và
câu hỏi hiện tại. 4. Nếu context rỗng, ghi rõ không có dữ liệu được truy
xuất.

### 5.2. `AIService.ask()`

1.  Kiểm tra query không rỗng.
2.  Gọi `build_messages()`.
3.  Gọi chat completion API với model được cấu hình.
4.  Lấy `response.choices[0].message.content`.
5.  Trả câu trả lời về `main.py`.

Nếu API lỗi, `main.py` bắt exception và gửi thông báo lỗi tới Discord.

**Quy tắc về câu "Không tìm thấy dữ liệu":** chỉ nên áp dụng khi
retrieval được thiết kế và đánh giá đủ tin cậy cho câu hỏi nghiệp vụ.
Context rỗng có thể do retriever yếu, entity filtering sai hoặc thiếu dữ
liệu đã index; không tự động chứng minh dữ liệu không tồn tại.

------------------------------------------------------------------------

## 6. MySQL session, messages và attachments

### 6.1. Khi nhận câu hỏi

1.  `get_or_create_session(user_id, channel_id)`.
2.  `save_message(role="user", content=query)`.
3.  Nếu message có attachments, lấy metadata từ Discord.
4.  Lưu từng attachment liên kết với `message_id`.
5.  Lấy lịch sử gần đây.
6.  Loại message hiện tại khỏi history trước khi gửi LLM.

### 6.2. Khi có câu trả lời

1.  `AIService.ask()` trả answer.
2.  Lưu answer với `role="assistant"`.
3.  Lưu metadata về retrieved documents.
4.  Gửi answer về Discord; nếu dài hơn giới hạn Discord, chia thành
    nhiều message.

### 6.3. Database khởi động

Mục tiêu vận hành: 1. Khởi động `main.py`. 2. Kiểm tra MySQL connection.
3. Tạo database nếu chưa có (nếu tài khoản có quyền). 4. Tạo tables nếu
chưa có. 5. Khởi tạo services và bắt đầu nhận message.

Cần bảo đảm schema và foreign key tương thích.
`CREATE TABLE IF NOT EXISTS` không tự sửa schema của bảng đã tồn tại.
Phải quản lý thay đổi schema rõ ràng, ví dụ bằng SQL versioning hoặc
migration script.

### 6.4. Lưu tệp đa phương tiện

Bảng `chat_attachments` lưu metadata như: - `message_id` - `filename` -
`content_type` - `media_type` - `size_bytes` - `storage_path` -
`source_url`

Đây không phải nội dung file. Nếu cần giữ file lâu dài, tải về storage
an toàn và lưu đường dẫn ổn định. Không tin cậy URL nguồn Discord như
đường dẫn lưu trữ vĩnh viễn.

### 6.5. Liên kết Discord với tài khoản Travel Planner

Schema hiện không có Discord ID trong bảng `users`, vì vậy không được
coi Discord snowflake là `users.id`. Bảng liên kết bổ sung nằm trong
`requirements/discord_user_links.sql`; migration tạo liên kết ở trạng
thái chưa kích hoạt mặc định.

Sau khi xác minh chủ tài khoản ngoài bot, người quản trị cài migration
bằng database Travel Planner và tạo một liên kết đã xác minh:

```sql
INSERT INTO discord_user_links (
    discord_user_id,
    travel_user_id,
    is_active,
    verified_by,
    verified_at
)
VALUES (
    '<discord_snowflake>',
    <travel_planner_user_id>,
    1,
    '<admin_discord_snowflake>',
    CURRENT_TIMESTAMP
);
```

Để thu hồi quyền, đặt `is_active = 0` cho Discord ID tương ứng. Bot lấy
Discord ID từ người gửi đã xác thực, tìm `travel_user_id` qua bảng liên
kết, rồi truyền ID đó làm tham số SQL. ID người dùng có trong nội dung
câu hỏi không bao giờ được dùng để cấp quyền.

Truy vấn booking lọc theo `bookings.user_id`; payment nối với booking
được sở hữu bởi người đó; expense và trip lọc qua `trips.user_id`. Mọi
giá trị đều được truyền bằng placeholder của MySQL Connector. Nếu chưa
có liên kết đang hoạt động, bot trả lời rằng không thể truy cập dữ liệu
riêng tư và không gọi mô hình để đoán.

------------------------------------------------------------------------

## 7. Debug case: booking query trả payments

Đây là lỗi đã quan sát ở phiên bản trước khi router được nối vào
`main.py`. Luồng runtime hiện tại phân biệt tra cứu đặt phòng, thanh
toán, chi phí và chuyến đi; các truy vấn riêng tư dùng SQL có kiểm tra
liên kết tài khoản thay vì lấy dữ liệu từ FAISS.

Query: `Tôi muốn xem đơn đặt phòng`

Kết quả đã quan sát trong debug: 1. Domain classifier dự đoán `booking`.
2. Candidate documents được giới hạn theo mapping booking, bao gồm
`bookings` và `payments`. 3. BM25 không tìm được từ khớp trong booking
document và trả rỗng. 4. Semantic retrieval trả về documents `payments`.
5. Hybrid retrieval trả kết quả semantic, do không có keyword results.

Diễn giải: - Domain classification đã giúp giới hạn lĩnh vực ở mức
rộng. - Domain `booking` vẫn chứa nhiều loại document. - Chưa có intent
để phân biệt "xem đơn đặt phòng" với "xem thanh toán". - Semantic score
cao nhất trong candidate set không bảo đảm document đúng nghiệp vụ.

Cách xử lý theo thứ tự: 1. Tạo và kiểm thử intent labels. 2. Chọn
document types dựa trên domain + intent. 3. Kiểm tra nội dung được
embedding và query representation. 4. Đánh giá keyword và semantic
riêng. 5. Sau khi có baseline, thử reranking hoặc rank fusion. 6. Viết
integration test xác minh loại document phù hợp, không chỉ xác minh
domain.

------------------------------------------------------------------------

## 8. Trạng thái tính năng

### Đã có trong code đã trao đổi

-   Discord event handling và provider initialization.
-   MySQL session/message CRUD cơ bản.
-   SQL → JSON script.
-   Embedding generation.
-   FAISS index load/create.
-   Domain classifier.
-   Entity extraction/resolution/filtering modules.
-   BM25 keyword retrieval.
-   Semantic retrieval.
-   Hybrid retrieval.
-   Context building và LLM request.

### Cần xác minh trong repository/runtime

-   Cấu hình và tên model provider còn hợp lệ.
-   Thứ tự vectors/document trong FAISS.
-   Chất lượng và coverage của training data.
-   Tính đầy đủ của parser SQL.
-   Chất lượng entity resolution trên các câu hỏi thực tế.
-   MySQL schema hiện có và quyền tạo database/tables.
-   Cách lưu attachment thực tế.

### Chưa có module riêng hoặc là kế hoạch

-   Tra cứu SQL giao dịch chỉ hoạt động khi quản trị viên đã chạy
    migration và tạo liên kết tài khoản đã xác minh.
-   Tự động khởi tạo database/schema (nếu chưa được bổ sung vào code).
-   Tải và lưu bền vững file attachments.
-   Phân tích nội dung ảnh/audio/video.
-   Đánh giá độ chính xác câu trả lời và tỷ lệ khẳng định không có bằng
    chứng trên Qwen.

------------------------------------------------------------------------

## 10. Hướng phát triển ưu tiên

1.  Ổn định quy trình dữ liệu SQL → JSON → embeddings → FAISS.
2.  Tạo quy trình quản trị để xác minh và liên kết tài khoản Discord.
3.  Hoàn thiện database initialization và attachment persistence.
4.  Điều khiển candidate document types bằng domain + intent + entity.
5.  Cải thiện và đánh giá hybrid retrieval trên tập câu hỏi lớn hơn.
6.  Đánh giá câu trả lời Qwen và tỷ lệ khẳng định không có bằng chứng.
7.  Thêm xử lý media khi có yêu cầu.

## 11. Hướng dẫn cho AI coding agent

Trước khi sửa code: 1. Đọc tài liệu này. 2. Kiểm tra source code hiện
tại và các file liên quan. 3. Xác định tính năng là đã có, cần xác minh
hay chỉ là đề xuất. 4. Tìm nơi gọi module và các thành phần liên quan.
5. Đề xuất thay đổi nhỏ, đúng phạm vi.

Khi thực hiện: - Giữ code rõ ràng, dễ hiểu và chia trách nhiệm theo
module. - Không tự ý đổi schema hoặc kiến trúc ngoài phạm vi yêu cầu. -
Không hard-code secrets. - Cập nhật tài liệu khi thay đổi hành vi. - Sửa
nguyên nhân gốc của retrieval sai, không chỉ nới assertions. - Khi thay
đổi dữ liệu/embedding model, kiểm tra việc rebuild embeddings và
FAISS. - Khi thay đổi schema, xem xét dữ liệu hiện có và quá trình khởi
động. - Cập nhật tài liệu này khi pipeline hoặc trạng thái triển khai
thay đổi.

## Tóm tắt

Hệ thống là Discord chatbot cho Travel Planner: classifier phân loại
domain và intent, router chọn giữa truy xuất công khai bằng BM25/FAISS
và tra cứu giao dịch bằng SQL có phân quyền, AIService gọi LLM, MySQL
lưu session/history và metadata attachments. Tra cứu dữ liệu riêng tư
cần có migration và liên kết tài khoản đã được xác minh.
