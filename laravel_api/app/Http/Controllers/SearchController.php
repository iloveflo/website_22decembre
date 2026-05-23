<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function getAll(Request $request)
    {
        // 1. Khởi tạo Query Builder (Giữ nguyên của bạn vì đã rất chuẩn)
        $query = Product::with(['images', 'variants', 'category'])
            ->join('categories as c', 'products.category_id', '=', 'c.id')
            ->leftJoin('categories as p', 'c.parent_id', '=', 'p.id')
            ->leftJoin('categories as gp', 'p.parent_id', '=', 'gp.id')
            ->select('products.*') 
            ->where('c.status', 'active')
            ->whereNull('c.deleted_at')
            ->where(function($q) {
                $q->whereNull('c.parent_id')
                  ->orWhere(function($sq) {
                      $sq->where('p.status', 'active')->whereNull('p.deleted_at');
                  });
            })
            ->where(function($q) {
                $q->whereNull('p.parent_id')
                  ->orWhere(function($sq) {
                      $sq->where('gp.status', 'active')->whereNull('gp.deleted_at');
                  });
            })
            ->where('products.status', 'active');

        // 2. Xử lý tìm kiếm nâng cao
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            // 2.1 "Bộ não" Ngữ nghĩa (Giữ nguyên)
            $synonyms = [
                'mùa hè'      => ['cotton', 'thấm hút', 'thoáng mát', 'đũi', 'linen', 'short', 'đùi', 'thun', 'ba lỗ', 'tanktop'],
                'mùa đông'    => ['ấm', 'len', 'nỉ', 'dày dặn', 'hoodie', 'khoác', 'jacket', 'bomber', 'giữ nhiệt'],
                'công sở'     => ['sơ mi', 'quần tây', 'kaki', 'âu', 'thanh lịch', 'đứng form', 'oxford', 'lịch sự'],
                'đi chơi'     => ['thun', 'jean', 'short', 'graphic', 'năng động', 'streetwear', 'oversize'],
                'thể thao'    => ['jogger', 'nỉ', 'gym', 'co giãn', 'thun', 'chạy bộ', 'thoáng khí', 'dry-fit'],
                'đi tiệc'     => ['vest', 'sơ mi', 'giày tây', 'sang trọng', 'thanh lịch'],
                'rộng'        => ['oversize', 'loose fit', 'thoải mái', 'ống rộng'],
                'ôm'          => ['slimfit', 'body', 'tôn dáng'],
                'ngắn tay'    => ['thun', 'phông', 'polo', 't-shirt'],
                'dài tay'     => ['sơ mi', 'len', 'hoodie', 'sweatshirt', 'khoác'],
                'phụ kiện'    => ['ví', 'thắt lưng', 'balo', 'túi', 'nón', 'tất', 'vớ', 'kính'],
            ];

            // 2.2 SỬA LỖI TypoMap BẰNG REGEX (Word Boundary)
            $typoMap = [
                'jean'  => 'jeans',
                't-shert' => 't-shirt',
                'giay'  => 'giày',
                'quan'  => 'quần',
                'ao'    => 'áo',
                'vj'    => 'ví',
                'that lung' => 'thắt lưng',
            ];

            foreach ($typoMap as $wrong => $right) {
                // Dùng \b để đánh dấu ranh giới từ. "ao" chỉ được đổi thành "áo" nếu nó đứng độc lập
                // Tránh lỗi vô tình đổi "dao" thành "dáo", "quan" thành "quang" -> "quầng"
                $pattern = "/\b" . preg_quote($wrong, '/') . "\b/ui";
                $keyword = preg_replace($pattern, $right, $keyword);
            }

            // Chia nhỏ và xử lý từ khóa thông minh
            $tempKeyword = $keyword;
            $expandedGroups = [];

            // 1. Kiểm tra cụm từ đặc biệt
            foreach ($synonyms as $key => $values) {
                if (mb_strpos(mb_strtolower($tempKeyword), mb_strtolower($key)) !== false) {
                    $expandedGroups[] = array_merge([$key], $values);
                    $tempKeyword = str_ireplace($key, '', $tempKeyword);
                }
            }

            // 2. Các từ còn lại
            $remainingWords = array_filter(explode(' ', $tempKeyword));
            foreach ($remainingWords as $word) {
                $expandedGroups[] = [$word];
            }

            // 3. SỬA LỖI: TÌM KIẾM PHÂN BIỆT DẤU (Accent-Sensitive) & KHÔNG PHÂN BIỆT HOA THƯỜNG (Case-Insensitive)
            if (!empty($expandedGroups)) {
                $query->where(function ($q) use ($expandedGroups) {
                    
                    // Nhóm 1: TRONG TÊN SẢN PHẨM
                    $q->where(function ($qName) use ($expandedGroups) {
                        foreach ($expandedGroups as $terms) {
                            $qName->where(function ($subGroup) use ($terms) {
                                foreach ($terms as $term) {
                                    // Bắt buộc dùng mb_strtolower để hạ case tiếng Việt chuẩn xác
                                    $searchTerm = '%' . mb_strtolower($term, 'UTF-8') . '%';
                                    // Dùng LOWER() và utf8mb4_bin để bắt MySQL phân biệt chính xác dấu
                                    $subGroup->orWhereRaw("LOWER(products.name) COLLATE utf8mb4_bin LIKE ?", [$searchTerm]);
                                }
                            });
                        }
                    })
                    // Nhóm 2: TRONG TÊN DANH MỤC
                    ->orWhere(function ($qCat) use ($expandedGroups) {
                        foreach ($expandedGroups as $terms) {
                            $qCat->where(function ($subGroup) use ($terms) {
                                foreach ($terms as $term) {
                                    $searchTerm = '%' . mb_strtolower($term, 'UTF-8') . '%';
                                    $subGroup->orWhereRaw("LOWER(c.name) COLLATE utf8mb4_bin LIKE ?", [$searchTerm]);
                                }
                            });
                        }
                    })
                    // Nhóm 3: TRONG MÔ TẢ
                    ->orWhere(function ($qDesc) use ($expandedGroups) {
                        foreach ($expandedGroups as $terms) {
                            $qDesc->where(function ($subGroup) use ($terms) {
                                foreach ($terms as $term) {
                                    $searchTerm = '%' . mb_strtolower($term, 'UTF-8') . '%';
                                    $subGroup->orWhereRaw("LOWER(products.description) COLLATE utf8mb4_bin LIKE ?", [$searchTerm]);
                                }
                            });
                        }
                    })
                    // Nhóm 4: TRONG SKU (Mã thì dùng Like thường là đủ)
                    ->orWhere(function ($qSku) use ($expandedGroups) {
                        foreach ($expandedGroups as $terms) {
                            $qSku->where(function ($subGroup) use ($terms) {
                                foreach ($terms as $term) {
                                    $subGroup->orWhere('products.sku', 'like', '%' . $term . '%');
                                }
                            });
                        }
                    });
                });
            }

            // 4. CẬP NHẬT LẠI THUẬT TOÁN SẮP XẾP ĐỂ KHÔNG BỊ LỆCH DẤU
            $lowerKeyword = mb_strtolower($keyword, 'UTF-8');
            $query->orderByRaw("CASE 
                WHEN LOWER(products.name) COLLATE utf8mb4_bin LIKE ? THEN 1 -- Khớp chính xác tên
                WHEN LOWER(products.name) COLLATE utf8mb4_bin LIKE ? THEN 2 -- Bắt đầu bằng keyword
                WHEN LOWER(c.name) COLLATE utf8mb4_bin LIKE ? THEN 3 -- Khớp danh mục
                ELSE 4 
            END", [$lowerKeyword, $lowerKeyword . '%', '%' . $lowerKeyword . '%']);
        }

        // Sắp xếp phụ theo ngày
        $query->orderBy('products.created_at', 'desc');

        // 4. Phân trang linh hoạt theo request
        $perPage = $request->input('per_page', 12);
        $products = $query->paginate($perPage);

        // Quan trọng: Giữ tham số trên URL cho các trang sau
        $products->appends($request->query());

        return response()->json($products);
    }
}