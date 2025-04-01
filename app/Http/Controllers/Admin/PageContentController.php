<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageContentController extends Controller
{
    /**
     * 显示页面内容列表
     */
    public function index()
    {
        // 检查权限
        if (!auth()->user()->can('manage_pages')) {
            abort(403, '您没有管理页面内容的权限');
        }

        $pages = PageContent::all();
        
        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * 显示编辑页面内容表单
     */
    public function edit($id)
    {
        // 检查权限
        if (!auth()->user()->can('manage_pages')) {
            abort(403, '您没有管理页面内容的权限');
        }

        $page = PageContent::findOrFail($id);
        
        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page,
        ]);
    }

    /**
     * 更新页面内容
     */
    public function update(Request $request, $id)
    {
        // 检查权限
        if (!auth()->user()->can('manage_pages')) {
            abort(403, '您没有管理页面内容的权限');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|array',
            'html_content' => 'nullable|string',
        ]);

        $page = PageContent::findOrFail($id);
        $page->update($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', '页面内容已更新');
    }
}
