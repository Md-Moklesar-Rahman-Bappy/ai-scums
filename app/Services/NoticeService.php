<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notice;
use App\Repositories\NoticeRepository;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * NoticeService.
 *
 * Manages announcements, events and notifications. Events (type='event') feed
 * the FullCalendar-based routine/event views.
 */
class NoticeService
{
    public function __construct(private readonly NoticeRepository $repository) {}

    /**
     * @return Paginator<Notice>
     */
    public function list(int $perPage = 15): Paginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Publish a notice.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Notice
    {
        $data['created_by'] = auth()->id();

        return $this->repository->create($data);
    }

    /**
     * Update a notice.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Notice $notice, array $data): Notice
    {
        return $this->repository->update($notice, $data);
    }

    /**
     * Soft-delete a notice.
     */
    public function delete(Notice $notice): void
    {
        $this->repository->delete($notice);
    }
}
