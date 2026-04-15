<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Models\PartnerContact;
use App\Models\PartnerInteraction;
use App\Http\Validations\PartnerValidation;
use App\Services\PartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PartnerController extends Controller
{
    public function __construct(
        private readonly PartnerService $partnerService,
        private readonly PartnerValidation $partnerValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->partnerValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->partnerService->getAll($request);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta']);
    }

    public function options(): JsonResponse
    {
        $result = $this->partnerService->getOptions();

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->partnerService->getById($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->partnerValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->partnerService->create($request->all());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->partnerValidation->updateValidation($request, $id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->partnerService->update($id, $request->all());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->partnerService->delete($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'DELETE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }

    public function storeContact(Request $request, int $id): JsonResponse
    {
        $validator = $this->partnerValidation->contactValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $partnerExists = DB::table('ipa_partner')->where('id', $id)->exists();
        if (! $partnerExists) {
            return $this->errorResponse(__('partners.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $contact = DB::transaction(function () use ($request, $id): PartnerContact {
            $isPrimary = $request->boolean('isPrimary', false);

            if ($isPrimary) {
                DB::table('ipa_partner_contact')
                    ->where('partner_id', $id)
                    ->update(['is_primary' => false, 'updated_at' => now()]);
            }

            return PartnerContact::create([
                'partner_id' => $id,
                'full_name' => (string) $request->input('fullName'),
                'title' => $request->input('title'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'is_primary' => $isPrimary,
            ]);
        });

        return $this->createdResponse([
            'id' => (string) $contact->id,
        ], __('partners.messages.contact_create_success'));
    }

    public function storeInteraction(Request $request, int $id): JsonResponse
    {
        $validator = $this->partnerValidation->interactionValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $partnerExists = DB::table('ipa_partner')->where('id', $id)->exists();
        if (! $partnerExists) {
            return $this->errorResponse(__('partners.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $ownerUserId = $this->resolveUserId($request);
        if ($ownerUserId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $interaction = PartnerInteraction::create([
            'partner_id' => $id,
            'interaction_type' => (int) $request->input('interactionType', 0),
            'interaction_at' => $request->input('interactionAt'),
            'owner_user_id' => $ownerUserId,
            'summary' => $request->input('summary'),
        ]);

        return $this->createdResponse([
            'id' => (string) $interaction->id,
        ], __('partners.messages.interaction_create_success'));
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
