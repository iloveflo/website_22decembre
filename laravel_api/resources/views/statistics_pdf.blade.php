<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo Cáo Hiệu Suất Kinh Doanh</title>
    <style>
        /* PDF Engine specific styles */
        @page {
            margin: 1cm;
        }

        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed, 
        figure, figcaption, footer, header, hgroup, 
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            font-family: "DejaVu Sans", sans-serif;
        }

        body { 
            font-size: 10px; 
            color: #1e293b; 
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .brand-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            letter-spacing: -1px;
        }

        .report-title {
            font-size: 18px;
            font-weight: normal; /* Dùng normal để tránh lỗi font Bold không hỗ trợ tiếng Việt */
            color: #0f172a;
            margin-bottom: 2px;
        }

        .period-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11px;
        }

        /* CLEARFIX */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* KPI SECTION */
        .kpi-container {
            width: 100%;
            margin-bottom: 30px;
        }

        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            text-align: left;
        }

        .kpi-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 5px;
        }

        .kpi-value {
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
        }

        /* TABLES */
        .section-header {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
        }

        th {
            background-color: #f1f5f9;
            color: #475569;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-indigo { color: #6366f1; }

        /* STATUS BADGES */
        .badge {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-info { background: #e0f2fe; color: #075985; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }

        /* FOOTER */
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <table style="width: 100%; border-bottom: 3px solid #6366f1; padding-bottom: 20px; margin-bottom: 30px;">
        <tr>
            <td style="vertical-align: top; border: none; padding: 0;">
                <div class="brand-name" style="font-size: 28px; font-weight: bold; color: #1e293b; margin: 0;">22.Décembre</div>
                <div class="brand-sub" style="font-size: 11px; color: #64748b;">Hệ thống quản lý kinh doanh thời trang</div>
            </td>
            <td style="text-align: right; vertical-align: top; border: none; padding: 0;">
                <div class="report-title" style="font-size: 22px; color: #0f172a; margin-bottom: 5px;">BÁO CÁO HIỆU SUẤT</div>
                <div class="period-badge" style="display: inline-block; background: #f1f5f9; color: #475569; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 12px; margin-bottom: 10px;">{{ $period_text }}</div>
                <div class="meta-info" style="font-size: 10px; color: #64748b; line-height: 1.4;">
                    Người xuất: <strong>{{ $exported_by }}</strong> <br>
                    Thời gian: <strong>{{ $exported_at }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="kpi-container">
        <tr>
            <td style="width: 25%; padding-right: 10px;">
                <div class="kpi-box">
                    <div class="kpi-label">Tổng Doanh Thu</div>
                    <div class="kpi-value">{{ number_format($overview['revenue']) }} VND</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 5px;">
                <div class="kpi-box">
                    <div class="kpi-label">Số Đơn Hàng</div>
                    <div class="kpi-value">{{ number_format($overview['orderCount']) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 5px;">
                <div class="kpi-box">
                    <div class="kpi-label">Khách Mua Hàng</div>
                    <div class="kpi-value">{{ number_format($overview['activeCustomers']) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 10px;">
                <div class="kpi-box">
                    <div class="kpi-label">Giá Trị Đơn TB</div>
                    <div class="kpi-value">{{ number_format($overview['averageOrderValue']) }} VND</div>
                </div>
            </td>
        </tr>
    </table>

    @if(isset($charts['revenue']) && $charts['revenue'])
    <div class="section-header">Xu hướng Doanh thu</div>
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ $charts['revenue'] }}" style="width: 100%; max-height: 250px; border-radius: 8px;">
    </div>
    @endif

    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 15px; border: none;">
                <div class="section-header">Sản Phẩm Bán Chạy</div>
                <table>
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th class="text-right">Đã Bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $product)
                        <tr>
                            <td class="text-bold">{{ $product->product_name }}</td>
                            <td class="text-right text-indigo text-bold">{{ $product->total_sold }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 15px; border: none;">
                <div class="section-header">Khách Hàng Thân Thiết</div>
                <table>
                    <thead>
                        <tr>
                            <th>Khách Hàng</th>
                            <th class="text-right">Chi Tiêu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $customer)
                        <tr>
                            <td class="text-bold">{{ $customer->full_name }}</td>
                            <td class="text-right text-indigo text-bold">{{ number_format($customer->total_spent) }} VND</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    @if((isset($charts['status']) && $charts['status']) || (isset($charts['payment']) && $charts['payment']))
    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="width: 50%; text-align: center; border: none;">
                <div class="section-header">Trạng Thái Đơn Hàng</div>
                <img src="{{ $charts['status'] }}" style="width: 180px;">
            </td>
            <td style="width: 50%; text-align: center; border: none;">
                <div class="section-header">Phương Thức Thanh Toán</div>
                <img src="{{ $charts['payment'] }}" style="width: 180px;">
            </td>
        </tr>
    </table>
    @endif

    <div class="section-header">Phân Bổ Phương Thức Thanh Toán</div>
    <table style="table-layout: fixed;">
        <thead>
            <tr>
                <th style="width: 40%;">Phương Thức</th>
                <th style="width: 25%;" class="text-center">Số Lượng Đơn</th>
                <th style="width: 35%;" class="text-right">Tổng Doanh Thu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentMethods as $method)
            <tr>
                <td class="text-bold">{{ strtoupper($method->payment_method) }}</td>
                <td class="text-center">{{ $method->count }}</td>
                <td class="text-right text-bold">{{ number_format($method->total) }} VND</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-header">Giao Dịch Gần Đây</div>
    <table style="table-layout: fixed;">
        <thead>
            <tr>
                <th style="width: 15%;">Mã Đơn</th>
                <th style="width: 30%;">Khách Hàng</th>
                <th style="width: 15%;">Ngày Tạo</th>
                <th style="width: 20%;" class="text-center">Trạng Thái</th>
                <th style="width: 20%;" class="text-right">Tổng Tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $order)
            @php
                $statusMap = [
                    'pending' => ['text' => 'Chờ xử lý', 'class' => 'badge-warning'],
                    'confirmed' => ['text' => 'Đã xác nhận', 'class' => 'badge-info'],
                    'processing' => ['text' => 'Đang xử lý', 'class' => 'badge-info'],
                    'shipping' => ['text' => 'Đang giao', 'class' => 'badge-info'],
                    'completed' => ['text' => 'Hoàn thành', 'class' => 'badge-success'],
                    'cancelled' => ['text' => 'Đã hủy', 'class' => 'badge-danger'],
                ];
                $st = $statusMap[$order->order_status] ?? ['text' => $order->order_status, 'class' => ''];
            @endphp
            <tr>
                <td class="text-bold">#{{ $order->order_code ?? $order->id }}</td>
                <td>{{ $order->user->full_name ?? ($order->email ?? 'Khách vãng lai') }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td class="text-center">
                    <span class="badge {{ $st['class'] }}">{{ $st['text'] }}</span>
                </td>
                <td class="text-right text-bold">{{ number_format($order->total_amount) }} VND</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        22.Décembre Store - Báo cáo được trích xuất vào {{ now()->format('H:i d/m/Y') }} <br>
        Tài liệu lưu trữ nội bộ
    </div>
</body>
</html>