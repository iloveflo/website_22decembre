# 👕 22.Décembre - Tiệm Đồ Vintage
**Hệ thống Website Thương Mại Điện Tử chuyên phân phối các sản phẩm thời trang Vintage cao cấp.**

---

## 📖 MỤC LỤC
1. [Giới thiệu Tổng quan](#-1-giới-thiệu-tổng-quan)
2. [Các Tính năng Chính](#-2-các-tính-năng-chính)
3. [Công nghệ Sử dụng (Tech Stack)](#-3-công-nghệ-sử-dụng)
4. [Kiến trúc Cơ sở dữ liệu (Database Schema)](#-4-kiến-trúc-cơ-sở-dữ-liệu)
5. [Cấu trúc Thư mục Hệ thống](#-5-cấu-trúc-thư-mục)
6. [Luồng Hoạt động (Workflows)](#-6-luồng-hoạt-động-nghiệp-vụ)
7. [Hướng dẫn Cài đặt & Triển khai nhanh qua Docker](#-7-hướng-dẫn-cài-đặt-bằng-docker-khuyên-dùng)
8. [Hướng dẫn Phát triển Local (Không dùng Docker)](#-8-hướng-dẫn-phát-triển-trên-local-development)
9. [Tài liệu API (Tham khảo)](#-9-tài-liệu-api-tiêu-biểu)
10. [Hướng dẫn Triển khai lên Production](#-10-hướng-dẫn-triển-khai-production)
11. [FAQ & Khắc phục sự cố](#-11-câu-hỏi-thường-gặp-faq--troubleshooting)
12. [Đóng góp & Bản quyền](#-12-bản-quyền-và-đóng-góp)

---

## 🎯 1. Giới thiệu Tổng quan

**22.Décembre** là dự án xây dựng một nền tảng thương mại điện tử chuyên nghiệp, tập trung vào thị trường thời trang Vintage. Hệ thống được thiết kế theo chuẩn kiến trúc **Client-Server (RESTful API)** hiện đại. 

Trong đó, toàn bộ logic nghiệp vụ, bảo mật và lưu trữ được xử lý bởi **Laravel 12** ở phía Backend. Phía Frontend cung cấp trải nghiệm mượt mà, không tải lại trang (SPA - Single Page Application) nhờ sức mạnh của **Vue.js 3** và **Vite**. Đặc biệt, dự án đã được đóng gói cẩn thận bằng **Docker & Docker Compose**, hỗ trợ Multi-stage build nhằm tạo ra một môi trường nhất quán, dễ dàng cho việc phát triển và triển khai ở bất kỳ đâu.

---

## ✨ 2. Các Tính năng Chính

Hệ thống được chia thành 2 phân hệ rõ rệt, phân cấp quyền bằng Navigation Guards (Frontend) và Middleware (Backend).

### 👥 2.1. Phân hệ Khách hàng (User/Guest)
- **Khám phá Sản phẩm:** 
  - Xem danh sách sản phẩm theo dạng Grid/List.
  - Lọc sản phẩm theo danh mục (Category).
  - Thanh tìm kiếm sản phẩm thông minh.
  - Trang chi tiết sản phẩm hiển thị bộ sưu tập ảnh (Gallery Slider).
  - Chọn các thuộc tính của sản phẩm như Kích cỡ (Size), Màu sắc (Color) – Quản lý qua Variant.
- **Giỏ hàng & Đặt hàng (Cart & Checkout):**
  - Khách vãng lai (Guest) vẫn có thể thêm sản phẩm vào giỏ (thông qua `cart_sessions` & LocalStorage).
  - Tính toán tự động tổng tiền, phí giao hàng (Shipping Fee), và khoản giảm giá.
  - Nhập và kiểm tra tính hợp lệ của Mã giảm giá (Coupons/Voucher).
  - Hỗ trợ thanh toán tích hợp cổng điện tử (Ví dụ: VNPay, Momo) hoặc COD.
- **Tài khoản & Bảo mật:**
  - Hệ thống xác thực bằng **Laravel Sanctum** (JWT/Token).
  - Hỗ trợ đăng nhập nhanh bằng Google/Facebook qua **Laravel Socialite**.
  - Tích hợp Captcha chống Bot rác ở các form đăng ký/liên hệ.
  - Chức năng "Quên mật khẩu" sẽ gửi mail cấp lại mã token (Lưu qua bảng `password_reset_tokens`).
  - Trang Profile: Cập nhật Avatar, Số điện thoại, Địa chỉ giao hàng mặc định.
- **Quản lý Đơn hàng & Hậu mãi:**
  - Tra cứu mã đơn hàng trực tiếp mà không cần đăng nhập.
  - Xem trạng thái đơn hàng (Pending, Processing, Shipped, Delivered, Cancelled).
  - Sau khi nhận hàng, khách hàng có thể tham gia **Đánh giá (Review)** và chấm điểm (Rating) cho sản phẩm.
  - Các trang thông tin tĩnh: Hướng dẫn chọn Size, Chính sách đổi trả, Chính sách giao hàng.

### 👑 2.2. Phân hệ Quản trị (Admin/Staff Dashboard)
- **Bảng điều khiển (Dashboard & Analytics):**
  - Các widget thống kê số liệu tổng quan: Tổng doanh thu, Số lượng đơn hàng, Lượng khách hàng mới.
  - Tích hợp biểu đồ trực quan vẽ bằng **Chart.js**.
- **Quản lý Hàng hóa (Catalog Management):**
  - **Quản lý Danh mục:** Tạo cấu trúc danh mục, danh mục cha/con.
  - **Quản lý Sản phẩm gốc:** Tạo sản phẩm mới (Tên, SKU, Giá nhập, Giá bán, Mô tả).
  - **Quản lý Biến thể (Variants):** Định nghĩa cấu hình thuộc tính dưới dạng JSON (vd: `{"Màu": "Đen", "Size": "XL"}`), gán Số lượng kho (Stock) và Giá cộng thêm cho mỗi biến thể.
  - **Quản lý Ảnh:** Đăng tải nhiều ảnh cho một sản phẩm, đánh dấu ảnh chính (Primary Image).
- **Quản lý Đơn hàng & Thanh toán (Order & Payment):**
  - Chuyển đổi trạng thái đơn hàng.
  - Theo dõi lịch sử giao dịch thanh toán trong bảng `payments` (Bank code, Transaction ID).
  - Tính năng Xuất Hoá Đơn (Export PDF Invoice) bằng thư viện `barryvdh/laravel-dompdf`.
- **Quản lý Khuyến mãi (Marketing/Coupons):**
  - Tạo mã code khuyến mãi, đặt loại giảm giá (Percent / Fixed Amount).
  - Đặt điều kiện: Giá trị đơn tối thiểu, Hạn mức giảm tối đa (Max discount).
  - Thiết lập số lượng sử dụng (Usage limit) và giới hạn ngày bắt đầu/kết thúc.
- **Quản lý Hệ thống & Người dùng:**
  - Cấp quyền Role: `admin`, `staff`, `user`.
  - Phân quyền nhân viên (Ví dụ Staff chỉ được quản lý danh mục và bài viết, Admin được xem báo cáo doanh thu).

---

## 🛠 3. Công nghệ Sử dụng

Dự án áp dụng chặt chẽ các công nghệ tân tiến nhất ở thời điểm hiện tại:

### 🎨 Frontend (SPA)
- **Vue.js 3:** Sử dụng triệt để Composition API (Setup script).
- **Vite:** Công cụ build siêu tốc, tối ưu hoá HMR trong lúc dev.
- **Vue Router (v4):** Định tuyến trang, Navigation Guards (Chặn route chưa đăng nhập).
- **Axios:** Xử lý HTTP Requests kết nối đến Laravel API, tích hợp Interceptors để đính kèm Bearer Token.
- **Tailwind CSS / CSS thuần:** Tuỳ biến UI.
- **Thư viện phụ:** 
  - `SweetAlert2` (Hiển thị popup cảnh báo).
  - `Chart.js` & `vue-chartjs` (Vẽ biểu đồ Báo cáo).
  - `vue-virtual-scroller` (Tối ưu render danh sách lớn).

### ⚙️ Backend (API Server)
- **Laravel 12 / PHP 8.2:** Khung sườn kiến trúc chính (MVC / Service Repository Pattern).
- **Laravel Sanctum:** Phát hành API Token an toàn.
- **Laravel Socialite:** OAuth2 (Google Login).
- **barryvdh/laravel-dompdf:** Xuất HTML sang định dạng PDF (Dành cho hoá đơn).
- **Mews Captcha:** Chống spam (Form đăng ký).

### 🐳 DevOps & Cơ sở hạ tầng
- **Docker & Docker Compose:** Container hóa môi trường.
- **Nginx:** Web server đảo ngược (Reverse Proxy) phục vụ tĩnh các file Vue.js và forward request PHP.
- **PHP-FPM:** Xử lý các request động của Laravel.
- **Supervisor:** Tiến trình giám sát giữ cho Nginx và PHP-FPM cùng hoạt động song song trong một container duy nhất.
- **MySQL 8.0:** Hệ quản trị CSDL quan hệ chính.

---

## 🗄 4. Kiến trúc Cơ sở dữ liệu

Database của dự án được thiết kế chặt chẽ và chuẩn hóa để hỗ trợ Ecommerce. Dưới đây là mô tả chi tiết các bảng (Tables) chính:

1. **Bảng `users`**:
   - Lưu trữ `username`, `email`, `password`, `full_name`, `phone`, `address`, `avatar`, `role` (admin/staff/user), `google_id` (nếu có).
2. **Bảng `categories`**:
   - Lưu `name`, `slug`, `parent_id` (Hỗ trợ danh mục lồng nhau), trạng thái hiển thị.
3. **Bảng `products`**:
   - Thông tin cơ bản: `name`, `slug`, `description`, `price`, `cost_price` (giá vốn), `sale_price` (giá KM), `sku`, trạng thái `status`.
4. **Bảng `product_variants` (Rất quan trọng):**
   - Lưu thông tin chi tiết từng phiên bản: `product_id`, `variant_attributes` (JSON lưu Size/Color), `sku` của biến thể, `quantity` (Tồn kho thực tế), `additional_price`.
5. **Bảng `product_images`**:
   - `image_path`, cờ `is_primary`, số thứ tự `sort_order` cho gallery.
6. **Bảng `cart_sessions`**:
   - Theo dõi giỏ hàng cả khi guest chưa đăng nhập (Dùng session ID).
7. **Bảng `orders` & `order_items`**:
   - Lưu thông tin người nhận, `subtotal`, `discount_amount`, `shipping_fee`, `total_amount`, `status`, `payment_status`.
   - Bảng con `order_items` sẽ snapshot (lưu cứng) lại thông tin `product_name`, `variant_info`, `price` tại thời điểm đặt hàng.
8. **Bảng `payments`**:
   - Lưu thông tin giao dịch điện tử: `transaction_id`, `bank_code`, `amount`, `payment_method`, `payload` (JSON response từ API ngân hàng).
9. **Bảng `coupons`, `user_coupons`, `coupon_usage`**:
   - Hệ thống phát hành mã giảm giá, theo dõi người dùng đã xài mã nào, mã còn bao nhiêu lượt.
10. **Bảng `reviews`**:
    - Khách đánh giá theo `order_id` và `product_id`. `rating` từ 1-5 sao, status kiểm duyệt (pending/approved/rejected).

---

## 📂 5. Cấu trúc Thư mục

```text
ThucTapChuyenNganh/
│
├── laravel_api/                # Toàn bộ mã nguồn Backend Laravel 12
│   ├── app/                    # Chứa Controllers, Models, Middleware...
│   ├── bootstrap/              # Core khởi động framework
│   ├── config/                 # Các tệp cấu hình hệ thống
│   ├── database/               # Migrations, Seeders, Factories
│   ├── public/                 # Chứa index.php gốc (nhưng sẽ bị Nginx override một phần)
│   ├── resources/              # Views (Email templates, PDF templates)
│   ├── routes/                 # Định tuyến API (api.php, web.php)
│   └── .env.example            # Tệp môi trường mẫu
│
├── vue_frontend/               # Toàn bộ mã nguồn Frontend Vue 3
│   ├── src/
│   │   ├── assets/             # Hình ảnh tĩnh, CSS toàn cục
│   │   ├── components/         # Các mảnh UI dùng chung (Header, Footer, Button...)
│   │   ├── routes/             # Cấu hình Vue Router (index.js phân quyền)
│   │   ├── views/              # Các trang chính (Home, Admin, Cart, Login...)
│   │   └── main.js             # Entry point khởi tạo Vue App
│   ├── package.json            # Quản lý thư viện NPM
│   └── vite.config.js          # Cấu hình Vite
│
├── docker-conf/                # File cấu hình Server dành cho Docker Container
│   ├── nginx.conf              # Cấu hình Route cho Nginx (Phục vụ file Vue tĩnh & PHP Proxy)
│   └── supervisord.conf        # Cấu hình quản lý Nginx & PHP-FPM chạy ngầm
│
├── init.sql                    # File Script CSDL mẫu để boot database khi chạy Docker
├── docker-compose.yml          # Tệp cấu hình các Dịch vụ (App Web & DB)
├── Dockerfile                  # Script kịch bản Multi-stage Build cho project
└── README.md                   # Tài liệu đọc bạn đang xem
```

---

## 🔄 6. Luồng Hoạt động Nghiệp vụ

1. **Khách truy cập Web:** Gửi Request qua trình duyệt tới cổng `8000`.
2. **Nginx (trong Docker App):** Nhận Request.
   - Nếu là các URL dành cho người dùng tĩnh (CSS/JS/Hình ảnh của Vue), Nginx trả thẳng từ thư mục `public/`.
   - Nếu là yêu cầu truy cập giao diện, Nginx sẽ fallback về file `index.html` của Vue (Tính năng tiêu chuẩn của SPA).
   - Nếu đường dẫn bắt đầu bằng `/api/...`, Nginx sẽ chuyển tiếp (Forward) nó qua **PHP-FPM** để Laravel xử lý.
3. **Laravel API:** 
   - Kiểm tra Auth qua Middleware (Token Sanctum hợp lệ không?).
   - Gọi Model lấy dữ liệu từ MySQL (`db` container ở cổng 3306).
   - Format lại data sang chuẩn JSON và trả về cho Frontend Axios.
4. **Vue.js:** 
   - Nhận JSON. Cập nhật State (Reactivity).
   - Tự động thay đổi giao diện DOM mà không cần reload.

---

## 🚀 7. Hướng dẫn Cài đặt bằng Docker (Khuyên dùng)

Đây là cách dễ nhất, hạn chế 99% các lỗi "Works on my machine" do khác biệt phiên bản PHP/Node.js/MySQL.

### Yêu cầu tiên quyết
Máy tính của bạn cần được cài đặt sẵn:
- **Docker Desktop** (hoặc Docker Engine trên Linux).
- **Docker Compose**.

### Các bước khởi chạy
1. **Clone mã nguồn:**
   ```bash
   git clone https://github.com/your-username/22.decembre-vintage.git
   cd ThucTapChuyenNganh
   ```

2. **Khởi tạo và Build các dịch vụ:**
   ```bash
   docker-compose up -d --build
   ```
   *Quá trình này sẽ mất một vài phút cho lần đầu tiên. Docker sẽ: Tải Node 20 -> Cài đặt NPM -> Build Vue -> Tải PHP 8.2 FPM -> Cài đặt Nginx/Supervisor -> Cài Composer Laravel -> Gộp vào một Image duy nhất -> Chạy Database MySQL 8.0.*

3. **Truy cập ứng dụng:**
   - Mở trình duyệt tại: **http://localhost:8000**
   - *Lưu ý quan trọng:* Trong quá trình khởi động container `db`, file `init.sql` sẽ tự động chạy để tạo toàn bộ Tables và đút sẵn Dữ liệu mẫu (Seed) vào cho bạn. Bạn có thể đăng nhập ngay bằng tài khoản Admin mặc định có trong file SQL.

4. **Kiểm tra Logs (Nếu cần bắt lỗi):**
   ```bash
   docker-compose logs -f app
   docker-compose logs -f db
   ```

5. **Tắt hệ thống:**
   ```bash
   docker-compose down
   ```

---

## 💻 8. Hướng dẫn Phát triển trên Local (Development)

Nếu bạn không muốn xài Docker hoặc muốn Code trực tiếp có tính năng Hot Reload (lưu file là trình duyệt tự cập nhật), bạn cần chia ra chạy 2 server ảo:

### ⚙️ Bước 8.1: Setup Backend (Laravel)
1. Cài đặt các công cụ: **PHP 8.2+, Composer, MySQL 8.0.**
2. Đi tới thư mục chứa Backend:
   ```bash
   cd laravel_api
   ```
3. Cài đặt thư viện bằng Composer:
   ```bash
   composer install
   ```
4. Cấu hình môi trường:
   ```bash
   cp .env.example .env
   ```
5. Mở file `.env` vừa tạo, cập nhật cấu hình Database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ten_database_cua_ban
   DB_USERNAME=root
   DB_PASSWORD=
   ```
6. Sinh khoá bảo mật và Migrate dữ liệu:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```
7. Chạy Server ở Port 8000:
   ```bash
   php artisan serve
   ```

### 🎨 Bước 8.2: Setup Frontend (Vue.js)
1. Cài đặt **Node.js (Bản 18 hoặc 20 LTS)**.
2. Mở 1 Terminal mới, đi tới thư mục Frontend:
   ```bash
   cd vue_frontend
   ```
3. Cài đặt các Package phụ thuộc:
   ```bash
   npm install
   ```
4. Mở file cấu hình Axios (thường ở `src/main.js` hoặc file axios config) và đảm bảo `baseURL` đang trỏ vào `http://127.0.0.1:8000/api`.
5. Khởi động Webpack/Vite Server:
   ```bash
   npm run dev
   ```
6. Web sẽ hiện ra ở **http://localhost:5173**. Hãy truy cập cổng này để code Frontend.

---

## 📡 9. Tài liệu API Tiêu biểu

Dự án cung cấp RESTful API đầy đủ. Dưới đây là vài Endpoint tham khảo:

| Endpoint | Method | Middleware | Mô tả |
|----------|--------|------------|-------|
| `/api/login` | POST | Public | Nhận email, password, trả về Bearer Token |
| `/api/register` | POST | Public | Đăng ký thành viên mới (có Captcha) |
| `/api/products` | GET | Public | Lấy danh sách sản phẩm (có phân trang) |
| `/api/products/{slug}`| GET | Public | Xem chi tiết sản phẩm + Biến thể |
| `/api/user/profile` | GET | auth:sanctum | Lấy thông tin user hiện tại |
| `/api/orders` | POST | auth:sanctum | Đặt hàng và lưu thông tin vào DB |
| `/api/admin/stats`| GET | role:admin | Lấy số liệu thống kê Dashboard |

*(Để xem toàn bộ API, hãy kiểm tra file `laravel_api/routes/api.php`)*

---

## ☁️ 10. Hướng dẫn Triển khai Production

Khi đẩy dự án lên một VPS thực tế (Ubuntu/Debian), cần làm các bước:

1. Copy toàn bộ code lên VPS.
2. Sửa file `docker-compose.yml`:
   - Thay đổi các biến môi trường thành mật khẩu khó đoán (`MYSQL_ROOT_PASSWORD`, `APP_KEY`).
   - Xoá mount file `init.sql` nếu không muốn xoá dữ liệu cũ.
3. Chạy lệnh: `docker-compose -f docker-compose.yml up -d --build`.
4. Cài đặt thêm Nginx ở máy chủ host (VPS) để làm Reverse Proxy (Cấu hình HTTPS với SSL Let's Encrypt), trỏ domain về `localhost:8000`.

---

## ❓ 11. Câu hỏi Thường gặp (FAQ) & Troubleshooting

**Q1: Chạy Docker xong nhưng lỗi không kết nối được Database?**
- Trả lời: Do MySQL container cần thời gian khởi động lâu hơn App container. Hãy chờ thêm 10-15 giây rồi F5 lại trang, hoặc dùng `depends_on` có check health trong docker-compose.

**Q2: Tại sao trang Admin bị trắng hoặc báo lỗi 401 Unauthorized?**
- Trả lời: Hãy chắc chắn bạn đã đăng nhập bằng một User có `role = admin`. Token sẽ được lưu ở LocalStorage, và Axios interceptor sẽ tự động đính kèm nó vào Header `Authorization: Bearer <token>`. Nếu token hết hạn, bạn sẽ bị đẩy về trang Login.

**Q3: Tôi sửa code Vue nhưng F5 trên trình duyệt cổng 8000 không thấy thay đổi?**
- Trả lời: Cổng 8000 là cổng của Docker (chạy bản Vue đã được Build cứng thành tĩnh). Nếu bạn muốn dev và thấy thay đổi ngay lập tức (Hot Reload), bạn phải xem Hướng dẫn số 8 (Chạy `npm run dev` ở cổng `5173`).

---

## 📜 12. Bản quyền và Đóng góp
- **Bản quyền:** 22.Décembre - Tiệm Đồ Vintage.
- **Mục đích:** Dự án được thực hiện để hoàn thiện yêu cầu Đồ án môn học / Thực tập chuyên ngành tại Đại Học / Cơ sở đào tạo. Tôn trọng mọi vấn đề liên quan đến bản quyền giáo dục.
- **Tác giả:** Xin xem danh sách đóng góp tại Github Insights hoặc qua file lịch sử Commit.
- Code được phân phối dưới giấy phép **MIT License** cho các thư viện mã nguồn mở.

Cảm ơn bạn đã quan tâm đến dự án. Chúc một ngày làm việc hiệu quả! 🚀

