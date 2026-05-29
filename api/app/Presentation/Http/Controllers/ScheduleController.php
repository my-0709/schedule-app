<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\Schedule\CreateScheduleDTO;
use App\Application\DTOs\Schedule\UpdateScheduleDTO;
use App\Application\UseCases\Schedule\CreateScheduleUseCase;
use App\Application\UseCases\Schedule\DeleteScheduleUseCase;
use App\Application\UseCases\Schedule\GetSchedulesUseCase;
use App\Application\UseCases\Schedule\UpdateScheduleUseCase;
use App\Presentation\Http\Requests\Schedule\CreateScheduleRequest;
use App\Presentation\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Presentation\Http\Resources\ScheduleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly GetSchedulesUseCase $getSchedulesUseCase,
        private readonly CreateScheduleUseCase $createScheduleUseCase,
        private readonly UpdateScheduleUseCase $updateScheduleUseCase,
        private readonly DeleteScheduleUseCase $deleteScheduleUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $schedules = $this->getSchedulesUseCase->execute($request->user()->id);

        return response()->json(
            array_map(fn($s) => (new ScheduleResource($s))->toArray($request), $schedules)
        );
    }

    public function store(CreateScheduleRequest $request): JsonResponse
    {
        $dto = new CreateScheduleDTO(
            userId: $request->user()->id,
            title: $request->input('title'),
            description: $request->input('description'),
            startAt: $request->input('start_at'),
            endAt: $request->input('end_at'),
            color: $request->input('color', 'indigo'),
        );

        $schedule = $this->createScheduleUseCase->execute($dto);

        return response()->json(
            (new ScheduleResource($schedule))->toArray($request),
            201
        );
    }

    public function update(UpdateScheduleRequest $request, int $id): JsonResponse
    {
        $dto = new UpdateScheduleDTO(
            title: $request->input('title'),
            description: $request->input('description'),
            startAt: $request->input('start_at'),
            endAt: $request->input('end_at'),
            color: $request->input('color', 'indigo'),
        );

        $schedule = $this->updateScheduleUseCase->execute($id, $request->user()->id, $dto);

        return response()->json(
            (new ScheduleResource($schedule))->toArray($request)
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->deleteScheduleUseCase->execute($id, $request->user()->id);

        return response()->json(['message' => 'スケジュールを削除しました。']);
    }
}
