<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\EventValidation;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly EventValidation $eventValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->eventValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->eventService->getAll($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->eventService->getById($id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse([
            'updated' => true,
            'event' => $result['data'],
        ], $result['message']);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->eventValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->eventService->create($request->all(), $this->resolveUserId($request));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validator = $this->eventValidation->updateValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->eventService->update($id, $request->all());

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function destroy(string $id): JsonResponse
    {
        $result = $this->eventService->delete($id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'DELETE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }

    public function join(Request $request, string $id): JsonResponse
    {
        $validator = $this->eventValidation->joinValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $userId = $this->resolveUserId($request);
        if ($userId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->eventService->join($id, $userId, (bool) $request->boolean('joined'));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function requestReschedule(Request $request, string $id): JsonResponse
    {
        $validator = $this->eventValidation->rescheduleValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $requestedBy = $this->resolveUserId($request);
        if ($requestedBy <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->eventService->requestReschedule($id, $request->all(), $requestedBy);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    private function resolveUserId(Request $request): int
    {
        $authenticatedUserId = (int) ($request->user()?->id ?? 0);

        if ($authenticatedUserId > 0) {
            return $authenticatedUserId;
        }

        if (! app()->environment(['local', 'development', 'testing'])) {
            return 0;
        }

        $mockUsername = trim((string) $request->header('X-Mock-Username', ''));
        $mockEmail = trim((string) $request->header('X-Mock-Email', ''));

        if ($mockUsername === '' && $mockEmail === '') {
            return 0;
        }

        $query = DB::table('ipa_user')->select('id');

        if ($mockUsername !== '' && $mockEmail !== '') {
            $query->where(function ($builder) use ($mockUsername, $mockEmail): void {
                $builder->where('username', $mockUsername)
                    ->orWhere('email', $mockEmail);
            });
        } elseif ($mockUsername !== '') {
            $query->where('username', $mockUsername);
        } else {
            $query->where('email', $mockEmail);
        }

        return (int) ($query->value('id') ?? 0);
    }
}