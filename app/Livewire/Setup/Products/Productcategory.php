<?php

namespace App\Livewire\Setup\Products;

use App\Models\productcategory as ProductCategoryModel;
use App\Models\products as ProductModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Productcategory extends Component
{
    use WithPagination;

    public $activeTab = 'categories';

    // Category fields
    public $category_name;

    public $category_code;

    public $category_description;

    public $category_type = 'mixed';

    public $category_status = 'active';

    public $editingCategoryId = null;

    // Product fields
    public $product_name;

    public $product_code;

    public $product_barcode;

    public $product_category_id;

    public $product_type = 'mixed';

    public $product_cost_price = '';

    public $product_selling_price = '';

    public $product_track_stock = false;

    public $product_is_serialized = false;

    public $product_requires_approval = false;

    public $product_reorder_level = 0;

    public $product_useful_life = 0;

    public $product_unit;

    public $product_status = 'active';

    public $product_description;

    public $editingProductId = null;

    // Search
    public $searchCategory = '';

    public $searchProduct = '';

    // Modal states
    public $showCategoryModal = false;

    public $showProductModal = false;

    // Delete confirmation
    public $confirmingDelete = false;

    public $deleteType = '';

    public $deleteId = '';

    protected $paginationTheme = 'bootstrap';

    public function updatedActiveTab()
    {
        $this->resetPage();
    }

    public function updatedSearchCategory()
    {
        $this->resetPage();
    }

    public function updatedSearchProduct()
    {
        $this->resetPage();
    }

    // Category Methods
    public function openCategoryModal($id = null)
    {
        $this->resetCategoryForm();
        if ($id) {
            $category = ProductCategoryModel::findOrFail($id);
            $this->editingCategoryId = $id;
            $this->category_name = $category->name;
            $this->category_code = $category->code;
            $this->category_description = $category->description;
            $this->category_type = $category->type;
            $this->category_status = $category->status;
        }
        $this->showCategoryModal = true;
    }

    public function resetCategoryForm()
    {
        $this->editingCategoryId = null;
        $this->category_name = '';
        $this->category_code = '';
        $this->category_description = '';
        $this->category_type = 'mixed';
        $this->category_status = 'active';
        $this->resetValidation(['category_name', 'category_code', 'category_description', 'category_type', 'category_status']);
    }

    public function saveCategory()
    {
        $this->validate([
            'category_name' => 'required|string|max:255',
            'category_code' => 'nullable|string|max:100|unique:productcategories,code,'.$this->editingCategoryId,
            'category_description' => 'nullable|string|max:500',
            'category_type' => 'required|in:good,asset,service,work,mixed',
            'category_status' => 'required|in:active,inactive',
        ]);

        $data = [
            'name' => $this->category_name,
            'code' => $this->category_code,
            'description' => $this->category_description,
            'type' => $this->category_type,
            'status' => $this->category_status,
        ];

        if ($this->editingCategoryId) {
            ProductCategoryModel::findOrFail($this->editingCategoryId)->update($data);
            session()->flash('success', 'Category updated successfully.');
        } else {
            $data['added_by'] = Auth::id();
            ProductCategoryModel::create($data);
            session()->flash('success', 'Category created successfully.');
        }

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    // Product Methods
    public function openProductModal($id = null)
    {
        $this->resetProductForm();
        if ($id) {
            $product = ProductModel::findOrFail($id);
            $this->editingProductId = $id;
            $this->product_name = $product->name;
            $this->product_code = $product->code;
            $this->product_barcode = $product->barcode;
            $this->product_category_id = $product->product_category_id;
            $this->product_type = $product->type;
            $this->product_cost_price = $product->cost_price;
            $this->product_selling_price = $product->selling_price;
            $this->product_track_stock = $product->track_stock;
            $this->product_is_serialized = $product->is_serialized;
            $this->product_requires_approval = $product->requires_approval;
            $this->product_reorder_level = $product->reorder_level;
            $this->product_useful_life = $product->useful_life;
            $this->product_unit = $product->unit;
            $this->product_status = $product->status;
            $this->product_description = $product->description;
        }
        $this->showProductModal = true;
    }

    public function resetProductForm()
    {
        $this->editingProductId = null;
        $this->product_name = '';
        $this->product_code = '';
        $this->product_barcode = '';
        $this->product_category_id = '';
        $this->product_type = 'mixed';
        $this->product_cost_price = '';
        $this->product_selling_price = '';
        $this->product_track_stock = false;
        $this->product_is_serialized = false;
        $this->product_requires_approval = false;
        $this->product_reorder_level = 0;
        $this->product_useful_life = 0;
        $this->product_unit = '';
        $this->product_status = 'active';
        $this->product_description = '';
        $this->resetValidation([
            'product_name', 'product_code', 'product_barcode', 'product_category_id',
            'product_type', 'product_cost_price', 'product_selling_price',
            'product_reorder_level', 'product_useful_life', 'product_unit',
            'product_status', 'product_description',
        ]);
    }

    public function saveProduct()
    {
        $this->validate([
            'product_name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:100|unique:products,code,'.$this->editingProductId,
            'product_barcode' => 'nullable|string|max:255',
            'product_category_id' => 'required|exists:productcategories,id',
            'product_type' => 'required|in:good,asset,service,work,mixed',
            'product_cost_price' => 'nullable|numeric|min:0',
            'product_selling_price' => 'nullable|numeric|min:0',
            'product_reorder_level' => 'nullable|integer|min:0',
            'product_useful_life' => 'nullable|integer|min:0',
            'product_unit' => 'nullable|string|max:50',
            'product_status' => 'required|in:active,inactive',
            'product_description' => 'nullable|string|max:500',
        ]);

        $data = [
            'name' => $this->product_name,
            'code' => $this->product_code,
            'barcode' => $this->product_barcode,
            'product_category_id' => $this->product_category_id,
            'type' => $this->product_type,
            'cost_price' => $this->product_cost_price !== '' ? $this->product_cost_price : null,
            'selling_price' => $this->product_selling_price !== '' ? $this->product_selling_price : null,
            'track_stock' => $this->product_track_stock,
            'is_serialized' => $this->product_is_serialized,
            'requires_approval' => $this->product_requires_approval,
            'reorder_level' => $this->product_reorder_level ?: 0,
            'useful_life' => $this->product_useful_life ?: 0,
            'unit' => $this->product_unit,
            'status' => $this->product_status,
            'description' => $this->product_description,
        ];

        if ($this->editingProductId) {
            ProductModel::findOrFail($this->editingProductId)->update($data);
            session()->flash('success', 'Product updated successfully.');
        } else {
            $data['added_by'] = Auth::id();
            ProductModel::create($data);
            session()->flash('success', 'Product created successfully.');
        }

        $this->showProductModal = false;
        $this->resetProductForm();
    }

    // Delete Methods
    public function confirmDelete($type, $id)
    {
        $this->deleteType = $type;
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->deleteType = '';
        $this->deleteId = '';
    }

    public function delete()
    {
        switch ($this->deleteType) {
            case 'category':
                ProductCategoryModel::findOrFail($this->deleteId)->delete();
                session()->flash('success', 'Category deleted successfully.');
                break;
            case 'product':
                ProductModel::findOrFail($this->deleteId)->delete();
                session()->flash('success', 'Product deleted successfully.');
                break;
        }

        $this->cancelDelete();
    }

    // Close modals
    public function closeModal()
    {
        $this->showCategoryModal = false;
        $this->showProductModal = false;
        $this->resetCategoryForm();
        $this->resetProductForm();
    }

    public function render()
    {
        $categories = ProductCategoryModel::withCount('products')
            ->when($this->searchCategory, fn ($q) => $q->where('name', 'like', '%'.$this->searchCategory.'%'))
            ->latest()
            ->paginate(10, ['*'], 'categoriesPage');

        $products = ProductModel::with('category')
            ->when($this->searchProduct, fn ($q) => $q->where('name', 'like', '%'.$this->searchProduct.'%'))
            ->latest()
            ->paginate(10, ['*'], 'productsPage');

        return view('livewire.setup.products.productcategory', [
            'categories' => $categories,
            'products' => $products,
            'categoriesForSelect' => ProductCategoryModel::orderBy('name')->get(),
        ]);
    }
}
