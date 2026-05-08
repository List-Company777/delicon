<?php
namespace App\Http\Controllers\Manage;

use App\Models\Cast;
use App\Models\CastSchedule;
use Illuminate\Http\Request;

class CastScheduleController extends BaseController
{
    public function index(int $castId)
    {
        $shop = $this->shopOrFail();
        $cast = Cast::where('shop_id', $shop->id)->findOrFail($castId);
        $schedules = CastSchedule::where('cast_id', $cast->id)
            ->where('work_date', '>=', today())
            ->orderBy('work_date')
            ->get();
        return view('manage.cast_schedule.index', compact('cast', 'schedules'));
    }

    public function store(Request $request, int $castId)
    {
        $shop = $this->shopOrFail();
        $cast = Cast::where('shop_id', $shop->id)->findOrFail($castId);

        $data = $request->validate([
            'work_date'  => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'note'       => ['nullable', 'string', 'max:100'],
        ], [
            'work_date.required'       => '日付を入力してください。',
            'work_date.after_or_equal' => '過去の日付は登録できません。',
        ]);

        // 同日重複チェック
        $exists = CastSchedule::where('cast_id', $cast->id)
            ->where('work_date', $data['work_date'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['work_date' => 'その日付はすでに登録されています。'])->withInput();
        }

        CastSchedule::create(['cast_id' => $cast->id] + $data);

        return back()->with('success', 'シフトを登録しました。');
    }

    public function destroy(int $castId, int $scheduleId)
    {
        $shop = $this->shopOrFail();
        $cast = Cast::where('shop_id', $shop->id)->findOrFail($castId);
        CastSchedule::where('cast_id', $cast->id)->findOrFail($scheduleId)->delete();
        return back()->with('success', 'シフトを削除しました。');
    }
}
