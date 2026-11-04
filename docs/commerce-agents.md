# Commerce Agents trong Travel Planner

Travel Planner dùng lõi Commerce Agents được sao chép từ bản local
`anthropics-commerce/commerce-agents`. Bản nguồn đã có OpenAI provider; tích hợp này
truyền `AsyncOpenAI` tường minh và gọi Responses API, không sử dụng Claude làm fallback.
Giấy phép Apache-2.0 và thông báo bản quyền được giữ trong thư mục plugin.

## Chạy bằng Docker

Đặt `OPENAI_API_KEY` trong `.env` ở thư mục gốc. Không commit file này.
`OPENAI_MODEL` mặc định là `gpt-4.1-mini`; có thể thay bằng model hỗ trợ Responses và function calling.

```powershell
docker compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.commerce.yml up -d --build
docker exec travel-planner-app php artisan commerce:index
docker exec --user www-data travel-planner-app php artisan search:index
```

Website: http://localhost:8080. Solr chỉ xuất cổng trên localhost:8983.
Overlay commerce build image local, cài Python riêng tại `/opt/commerce-venv`,
truyền key bằng biến môi trường runtime, giữ volume MySQL của compose hiện có.
Không chạy `down -v` khi cần giữ dữ liệu.

Database trống sẽ trả về danh mục trống. Tích hợp không tự tạo điểm đến hoặc đặt phòng giả.
Chỉ khi muốn dữ liệu demo mới chạy `php artisan db:seed` trong container.

## Kiến trúc

`POST /commerce-api/{role}/chat` → `CommerceAgentController` → `AiTravelAssistantInterface` →
`AiTravelAssistantService` → tiến trình Python → framework shopping/merchant.
Python yêu cầu công cụ qua JSON Lines; Laravel thực thi bằng `CommerceBackendService`
với người dùng lấy từ session, rồi trả kết quả qua stdin. Không mở endpoint database,
không cho model chạy SQL/shell, không truyền API key vào prompt hoặc trình duyệt.

Transcript công cụ, provenance, giỏ kế hoạch, bộ nhớ sở thích và đề xuất được lưu
trong session phía server. JSON gốc được giữ để bảo toàn `{}` và `[]` khi chuyển
giữa PHP và Pydantic. Các lượt chat/duyệt khóa session để tránh ghi đè đồng thời.
Đổi vai trò hoặc người dùng sẽ bắt đầu ngữ cảnh riêng.

## Khả năng được nối với website

| Phần | Dữ liệu / hành động |
|---|---|
| Khám phá | Tìm kiếm Solr, hydrate điểm đến, thành phố, danh mục, mô tả, phí vào cổng và các phòng từ database |
| Tư vấn | So sánh, hướng dẫn, kế hoạch và lịch trình nhiều ngày từ dữ liệu có provenance |
| Khách đăng nhập | 10 chuyến gần nhất, tối đa 30 hoạt động/chuyến, tổng chi phí, 10 chi phí gần nhất, 10 yêu thích; booking của chính khách |
| Giỏ | Kế hoạch tạm trong session, số lượng có giới hạn, link đến trang điểm đến/đặt phòng |
| Bộ nhớ | Các facts do công cụ memory của framework xác thực, giữ trong phiên; không tự trích xuất bằng Claude |
| Merchant (admin) | Tổng hợp booking/thanh toán 30 ngày, chuỗi số liệu theo ngày, cảnh báo quỹ phòng thấp, booking pending lâu |
| Merchant ghi | Đề xuất sửa tên/mô tả, giá trong ±20%, thêm quỹ phòng; nút Duyệt mới ghi DB và Activity Log |

Khung chat có ở trang chủ và layout quản trị. Presentation được hiển thị bằng DOM
với `textContent`; chỉ cho mở liên kết cùng origin. ID và quyền admin do server quyết định.
Agent không tự thanh toán, tạo booking, hoặc ghi lịch trình vào chuyến đi.
Giá phòng là mỗi đêm; phòng trống theo ngày được xác nhận tại luồng đặt phòng hiện có.

Các ví dụ gốc về campaign, promotion, tracking logistics, SQL analysis hoặc traffic
không có phân hệ tương ứng trong website nên không được giả lập là đã tích hợp.
Campaign tools và analysis delegate bị tắt; tool không được hỗ trợ trả thông báo rõ ràng.
Chính sách hủy/hoàn tiền chưa có nguồn chính thức thì agent không tự đặt ra.
Thông tin hướng dẫn website hiện lấy từ bản mô tả chức năng trong backend, không crawl
tùy ý tất cả URL và không đọc bảng chứa bí mật đăng nhập.

## Chỉ mục

Core `travel_destinations` dùng schema tại `deploy/solr/conf`.
`commerce:index` lập chỉ mục theo lô; dữ liệu kết quả luôn đọc lại từ DB.
Observer cập nhật chỉ mục sau commit khi sửa điểm đến, tên thành phố hoặc danh mục,
và xóa tài liệu khi xóa điểm đến. Nếu Solr lỗi, có warning để chạy lại `commerce:index`;
không thay Solr bằng truy vấn LIKE. Thay đổi qua SQL trực tiếp cần chạy lại lệnh index.

## Kiểm thử

```powershell
docker exec --user www-data -e APP_ENV=testing -e CACHE_STORE=array -e SESSION_DRIVER=array travel-planner-app php artisan test --do-not-cache-result --filter="CommerceAgentTest"
```

`APP_ENV=testing` cần được truyền tường minh do compose đặt `APP_ENV=local`.
Chạy bằng `www-data` để không tạo log/cache thuộc root khi Apache cần ghi các file này.
PHPUnit dùng SQLite memory theo cấu hình dự án, không làm trống MySQL.
Khóa API thiếu hoặc lỗi provider trả 503 và không lưu một lượt hội thoại thất bại.

Tài liệu OpenAI: https://developers.openai.com/api/docs/guides/function-calling
