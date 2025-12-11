<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\HasImageUpload; // Make sure the trait is imported
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    use HasImageUpload;
   // عرض صفحة الصنفات مع الفلترة
    public function index(Request $request)
    {
        $query = category::query();
        


    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where('type', 'LIKE', "%$search%");
              
    }
    
        $categories = $query->get();
  
        return view('admin.categories.categories', compact('categories'));
    }

    // صفحة إضافة صنف
    public function create()
    {
        return view('admin.categories.create_category');
    }

    // حفظ صنف جديد
    public function store(CategoryRequest $request)
    {
        // استدعاء التريت
        $this->handleImageCreation($request, Category::class, 'categories');
        return redirect()->route('categories.index')->with('success', 'category created successfully.');
    }

    // صفحة تعديل صنف
    public function edit(category $category)
    {
        return view('admin.categories.edit_category', compact('category'));
    }

    // تحديث صنف
    public function update(CategoryRequest $request, category $category)
    {

       $this->handleImageUpdate($request, $category , 'categories');
        return redirect()->route('categories.index')->with('success', 'category updated successfully.');
    }

    // حذف صنف
    public function destroy(category $category)
    {
        if ($category->image) {
            // حذف الصورة من التخزين
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'category deleted successfully.');
    }

}
