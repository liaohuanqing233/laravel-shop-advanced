<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    use HasResourceActions;

    /**
     * 列表页
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('商品类目列表')
            ->body($this->grid());
    }

    /**
     * 编辑页
     * @param $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑商品类目')
            ->body($this->form(true)->edit($id));
    }

    /**
     * 新规页
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建商品类目')
            ->body($this->form());
    }

    /**
     * 列表
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category);

        $grid->id('ID')->sortable();
        $grid->name('名称');
        $grid->level('层级');
        $grid->is_directory('是否为目录')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->path('目录路径');
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * 新规和编辑表单规则
     * @param bool $isEdit
     * @return Form
     */
    protected function form($isEdit = false)
    {
        $form = new Form(new Category);

        $form->text('name', '类目名称')->rules('required');

        //如果是编辑的话不允许编辑是否为目录和父类目字段值
        if ($isEdit) {
            $form->display('is_directory', '是否为目录')->with(function ($value) {
                return $value ? '是' : '否';
            });
            $form->display('parent.name', '父类目');
        } else {
            $form->radio('is_directory', '是否为目录')
                ->options(['1' => '是', '0' => '否'])
                ->default(0)
                ->rules('required');
            $form->select('parent_id', '父类目')->ajax('/admin/api/categories');//定义下拉框
        }

        return $form;
    }

    /**
     * 定义下拉搜索框接口
     * @param Request $request
     * @return mixed
     */
    public function apiIndex(Request $request)
    {
        $search = $request->input('q');
        $result = Category::query()
            ->where('is_directory', true)
            ->where('name', 'like', '%' . $search . '%')
            ->paginate();

        //格式组装
        $result->setCollection($result->getCollection()->map(function (Category $category) {
            return ['id' => $category->id, 'text' => $category->full_name];
        }));

        return $result;
    }
}
