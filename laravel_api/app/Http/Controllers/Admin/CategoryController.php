<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Danh sách cây danh mục (cha + con)
    public function tree()
    {
        $categories = Category::withCount(['products', 'children'])
            ->with(['children' => function ($q) {
                $q->withCount(['products'])->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Cộng dồn số lượng sản phẩm từ con lên cha
        $categories->each(function($parent) {
            $parent->products_count = $parent->products_count + $parent->children->sum('products_count');
        });

        return response()->json($categories);
    }

    // Danh sách phẳng
    public function index()
    {
        $categories = Category::withCount(['products'])
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    // Thêm mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Kiểm tra cấp độ (Tối đa 2 cấp)
        if ($validated['parent_id'] !== null) {
            $parentCategory = Category::find($validated['parent_id']);
            if ($parentCategory && $parentCategory->parent_id !== null) {
                return response()->json(['message' => 'Danh mục cha được chọn đang là danh mục con. Hệ thống chỉ hỗ trợ tối đa 2 cấp.'], 422);
            }
        }

        // Kiểm tra trùng slug
        $count = Category::where('slug', 'LIKE', $validated['slug'] . '%')->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    // Cập nhật
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // 1. Không cho phép chọn chính mình làm cha
        if ($validated['parent_id'] == $id) {
            return response()->json(['message' => 'Danh mục cha không thể là chính nó'], 422);
        }

        // 2. Nếu danh mục đang có con, thì nó KHÔNG ĐƯỢC PHÉP có cha (Vì hệ thống chỉ hỗ trợ 2 cấp)
        if ($validated['parent_id'] !== null) {
            $hasChildren = Category::where('parent_id', $id)->exists();
            if ($hasChildren) {
                return response()->json(['message' => 'Danh mục này đang chứa danh mục con, không thể chuyển nó thành danh mục con của nhóm khác (Tối đa 2 cấp)'], 422);
            }
        }

        // 3. Nếu danh mục cha được chọn ĐANG LÀ CON của một nhóm khác, thì không cho phép (Tối đa 2 cấp)
        if ($validated['parent_id'] !== null) {
            $parentCategory = Category::find($validated['parent_id']);
            if ($parentCategory && $parentCategory->parent_id !== null) {
                return response()->json(['message' => 'Danh mục cha được chọn đang là danh mục con. Hệ thống chỉ hỗ trợ tối đa 2 cấp.'], 422);
            }
        }

        $validated['slug'] = Str::slug($validated['name']);
        
        $category->update($validated);
        return response()->json($category);
    }

    // Xóa (Logic nhạy cảm)
    public function destroy($id)
    {
        $category = Category::withCount(['children', 'products'])->findOrFail($id);

        // 1. Kiểm tra nếu có danh mục con
        if ($category->children_count > 0) {
            return response()->json([
                'message' => 'Không thể xóa danh mục này vì vẫn còn danh mục con bên trong. Hãy xóa hoặc di chuyển các danh mục con trước.'
            ], 422);
        }

        // 2. Kiểm tra nếu có sản phẩm
        if ($category->products_count > 0) {
            return response()->json([
                'message' => 'Không thể xóa danh mục này vì đang có ' . $category->products_count . ' sản phẩm thuộc danh mục này. Hãy di chuyển sản phẩm sang danh mục khác trước.'
            ], 422);
        }

        $category->delete(); // Soft delete
        return response()->json(['message' => 'Xóa danh mục thành công']);
    }

    // Cây danh mục công khai (Chỉ hiện Active cho khách hàng)
    public function publicTree()
    {
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('status', 'active')->withCount('products')->orderBy('name');
            }])
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $categories->each(function($parent) {
            $parent->products_count = $parent->products_count + $parent->children->sum('products_count');
        });

        return response()->json($categories);
    }
}
