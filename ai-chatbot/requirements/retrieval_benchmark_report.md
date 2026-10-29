# Báo cáo đánh giá truy xuất tiếng Việt

Ngày ghi nhận: 2026-10-28

## Tập đánh giá

- 12 câu hỏi có mã tài liệu liên quan được ghi rõ.
- Có câu hỏi trực tiếp, diễn đạt lại, thiếu dấu, mã định danh, câu nối
  tiếp, tên địa điểm dễ nhầm và câu không có tài liệu phù hợp.
- Nguồn dữ liệu hiện có 443 tài liệu trước khi loại các bảng riêng tư.
- Bộ đo: `data/retrieval_evaluation.json`.
- Máy đo: Intel Core i7-1360P, RAM 15.6 GiB; không phát hiện `nvidia-smi`.

## Kết quả

| Cấu hình | Recall@5 | Recall@10 | MRR | Precision@5 | Độ trễ trung bình | Bộ nhớ |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| A. mô hình MiniLM và chỉ mục cũ | 0.700 | 0.867 | 0.744 | 0.220 | 43.9 ms | chưa đo |
| B. E5, không dùng lịch sử câu nối tiếp | 0.767 | 0.867 | 0.753 | 0.220 | 68.4 ms | chưa đo |
| C. E5, mở rộng bằng hai cặp hỏi–đáp gần nhất | 0.800 | 0.967 | 0.803 | 0.240 | 214.8 ms | chưa đo |

Mục A dùng mô hình `all-MiniLM-L6-v2` và chỉ mục 384 chiều cũ, cùng bộ
câu hỏi và thứ hạng RRF. Adapter đo khôi phục bộ tách token cũ; các bước
bảo vệ dữ liệu riêng tư hiện tại vẫn được áp dụng. Vì mã gốc trước thay
đổi không còn trong kho quản lý phiên bản, đây là baseline theo mô hình
và chỉ mục cũ, không phải bản sao đầy đủ của chương trình cũ.

Mô hình E5 đã được tải và tạo chỉ mục riêng 410 tài liệu công khai. Bộ
kiểm thử đầy đủ hiện đạt 46/46. Số đo bộ nhớ không có trong các kết quả
đã lưu.

Baseline chạy bằng `python rag/evaluate_retrieval.py --variant legacy
--label legacy_minilm`; B/C chạy trên cùng bộ 12 câu với chỉ mục E5.
Đo hiện tại dùng router mới: câu riêng tư được chuyển sang tuyến tra cứu
SQL và không lấy tài liệu từ chỉ mục RAG công khai. Bộ benchmark không
cung cấp danh tính đã liên kết nên không thực thi SQL giao dịch; hai câu
không có tài liệu vẫn không trả tài liệu ở cả ba lần đo. Mục C chỉ khác
mục B ở việc bật viết lại câu nối
tiếp; bộ dữ liệu chỉ có một câu loại này. Mức tăng trên bộ nhỏ này chưa
đủ để khẳng định hiệu quả tổng quát. Lần đo C mới dùng tối đa hai cặp
hỏi–đáp gần nhất để mở rộng câu hỏi nối tiếp. Độ trễ tăng so với lần đo B;
cần tập kiểm thử lớn hơn và nhiều lần chạy để xác định mức ảnh hưởng ổn
định. Baseline giữ tokenizer cũ và model/
index cũ nhưng dùng các bước an toàn dữ liệu hiện tại; vì vậy không phải
phép so sánh chỉ thay riêng embedding model. BGE-M3 chưa được benchmark.

## Cách tạo số liệu

1. Tải mô hình E5 theo hướng dẫn trong `requirements/workflows_developed.md`.
2. Chạy `python rag/build/build_embeddings_by_json.py`.
3. Chạy `python rag/vectordb/build_faiss_index.py`.
4. Đo B bằng `python rag/evaluate_retrieval.py --variant current --follow-up-rewrite off --label multilingual_e5_hybrid`.
5. Đo C bằng lệnh tương tự với `--follow-up-rewrite on`.
6. Giữ nguyên cùng tập đánh giá và dữ liệu khi so sánh các cấu hình.

Kết quả được ghi vào `data/retrieval_evaluation_results.json`. Có thể
chạy lại baseline bằng `python rag/evaluate_retrieval.py --variant legacy
--label legacy_minilm`. Không thay thế chỉ mục cũ cho tới khi chỉ mục
mới được kiểm tra.
