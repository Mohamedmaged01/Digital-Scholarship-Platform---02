<?php

namespace App\Http\Controllers;

use App\Support\Assistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:2', 'max:500']]);

        $result = Assistant::ask($data['q']);

        if ($result['entry']) {
            return response()->json(['answer' => $result['entry']->answer]);
        }

        return response()->json([
            'answer' => null,
            'message' => 'لم أجد إجابة دقيقة في قاعدة المعرفة لسؤالك، وقد سجّلته ليقوم فريق الإدارة بتزويد المساعد بها قريبًا. يمكنك التواصل مباشرة مع الإدارة عبر القنوات التالية:',
            'suggestions' => $result['suggestions'],
        ]);
    }
}
