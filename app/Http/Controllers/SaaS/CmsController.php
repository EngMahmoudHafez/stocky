<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;
use App\Models\CmsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CmsController extends Controller
{
    /**
     * Human-readable labels per section.
     */
    private const LABELS = [
        'features'          => ['singular' => 'Feature',       'plural' => 'Feature items'],
        'stats'             => ['singular' => 'Stat',          'plural' => 'Stat items'],
        'steps'             => ['singular' => 'Step',          'plural' => 'Step items'],
        'how-it-works-steps'=> ['singular' => 'Step',          'plural' => 'Step items'],
        'testimonials'      => ['singular' => 'Testimonial',   'plural' => 'Testimonials'],
        'faq'               => ['singular' => 'FAQ',           'plural' => 'Questions'],
        'footer_columns'    => ['singular' => 'Column',        'plural' => 'Footer columns'],
        'footer_social'     => ['singular' => 'Social link',   'plural' => 'Social links'],
    ];

    /**
     * Which fields the modal shows per section.
     */
    private const FIELDS = [
        'features'          => ['title','subtitle','icon','color','is_active'],
        'stats'             => ['value','title','icon','color','is_active'],
        'steps'             => ['title','subtitle','icon','color','is_active'],
        'how-it-works-steps'=> ['title','subtitle','icon','color','is_active'],
        'testimonials'      => ['title','subtitle','body','rating','image','is_active'],
        'faq'               => ['title','body','is_active'],
        'footer_columns'    => ['title','body','is_active'],
        'footer_social'     => ['title','url','icon','is_active'],
    ];

    private function check(Request $r)
    {
        if (!$r->session()->get('saas_super_admin')) {
            abort(redirect('/super/login'));
        }
    }

    public function list(Request $r, string $section)
    {
        $this->check($r);

        $items = CmsItem::section($section)->orderBy('sort_order')->orderBy('id')->get();
        $meta  = self::LABELS[$section] ?? ['singular' => 'Item', 'plural' => 'Items'];
        $fields = self::FIELDS[$section] ?? ['title','subtitle','body','icon','is_active'];

        return view('saas.cms._crud', compact('section', 'items', 'meta', 'fields'));
    }

    public function store(Request $r, string $section)
    {
        $this->check($r);

        $data = $r->only(['title','subtitle','body','icon','color','image','value','url','rating']);
        $data['section']    = $section;
        $data['is_active']  = $r->boolean('is_active', true);
        $data['sort_order'] = (int) CmsItem::section($section)->max('sort_order') + 1;

        CmsItem::create($data);

        return back()->with('success', (self::LABELS[$section]['singular'] ?? 'Item') . ' added.');
    }

    public function update(Request $r, string $section, $id)
    {
        $this->check($r);

        $item = CmsItem::section($section)->findOrFail($id);

        $data = $r->only(['title','subtitle','body','icon','color','image','value','url','rating']);
        $data['is_active'] = $r->boolean('is_active', true);

        $item->update($data);

        return back()->with('success', (self::LABELS[$section]['singular'] ?? 'Item') . ' updated.');
    }

    public function destroy(Request $r, string $section, $id)
    {
        $this->check($r);

        CmsItem::section($section)->where('id', $id)->delete();

        return back()->with('success', (self::LABELS[$section]['singular'] ?? 'Item') . ' deleted.');
    }

    public function toggle(Request $r, string $section, $id)
    {
        $this->check($r);

        $item = CmsItem::section($section)->findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        return back()->with('success', (string) ($item->is_active ? 'Activated.' : 'Deactivated.'));
    }
}
