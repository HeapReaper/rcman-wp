<?php

namespace RCMan\Admin\Pages;

use RCMan\Admin\ListTables\MembersListTable;
use RCMan\Capabilities;
use RCMan\Enums\ClubRole;
use RCMan\Enums\InstructorType;
use RCMan\Repositories\MemberRepository;
use RCMan\Services\MemberFormData;
use RCMan\Support\Logger;
use RCMan\Support\Request;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Members overview, view, add, edit and delete (port of UsersController + ShowAndSearchUsers).
 */
final class MembersPage extends AbstractPage
{
    private MemberRepository $members;

    public function __construct()
    {
        $this->members = new MemberRepository();
    }

    public function slug(): string
    {
        return 'rcman-members';
    }

    public function title(): string
    {
        return __('Leden', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Leden', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::VIEW_MEMBERS;
    }

    public function actions(): array
    {
        return [
            'rcman_save_member'   => 'handleSave',
            'rcman_delete_member' => 'handleDelete',
        ];
    }

    public function onLoad(): void
    {
        add_screen_option('per_page', [
            'label'   => __('Leden per pagina', 'rcman'),
            'default' => 20,
            'option'  => 'rcman_members_per_page',
        ]);
    }

    public function render(): void
    {
        // phpcs:disable WordPress.Security.NonceVerification
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';
        $id     = isset($_GET['id']) ? absint($_GET['id']) : 0;
        // phpcs:enable

        if ($action === 'view' && $id) {
            $this->renderView($id);
            return;
        }

        if (in_array($action, ['new', 'edit'], true) && current_user_can(Capabilities::EDIT_MEMBERS)) {
            $this->renderForm($action === 'edit' ? $id : 0);
            return;
        }

        $table = new MembersListTable();
        $table->prepare_items();

        View::render('admin/members/index', [
            'page'  => $this,
            'table' => $table,
        ]);
    }

    private function renderView(int $id): void
    {
        $member = $this->members->find($id);

        if (!$member) {
            wp_die(esc_html__('Lid niet gevonden.', 'rcman'));
        }

        View::render('admin/members/view', [
            'page'        => $this,
            'member'      => $member,
            'roles'       => $this->members->roles($id),
            'licences'    => $this->members->licences($id),
            'instructor'  => $this->members->instructorTypes($id),
        ]);
    }

    private function renderForm(int $id): void
    {
        $member = $id ? $this->members->find($id) : null;

        if ($id && !$member) {
            wp_die(esc_html__('Lid niet gevonden.', 'rcman'));
        }

        View::render('admin/members/form', [
            'page'              => $this,
            'member'            => $member,
            'member_roles'      => $id ? $this->members->roles($id) : [],
            'member_licences'   => $id ? wp_list_pluck($this->members->licences($id), 'id') : [],
            'member_instructor' => $id ? $this->members->instructorTypes($id) : [],
            'all_roles'         => ClubRole::options(),
            'all_licences'      => $this->members->allLicences(),
            'instructor_types'  => InstructorType::options(),
        ]);
    }

    public function handleSave(): void
    {
        $this->authorize('rcman_save_member', Capabilities::EDIT_MEMBERS);

        $id   = Request::int('id');
        $form = MemberFormData::fromRequest(false);

        if (!$form->isValid()) {
            $this->back(null, implode(' ', $form->errors));
        }

        if ($id) {
            if (!$this->members->find($id)) {
                $this->redirect([], null, __('Lid niet gevonden.', 'rcman'));
            }

            $this->members->update($id, $form->data, $form->roles, $form->licenceIds, $form->instructorTypes);
            Logger::info('Lid bijgewerkt', ['member_id' => $id, 'by' => $this->currentUserName()]);
            $this->redirect([], __('Lid is bijgewerkt!', 'rcman'));
        }

        $newId = $this->members->create($form->data, $form->roles, $form->licenceIds, $form->instructorTypes);
        Logger::info('Lid aangemaakt', ['member_id' => $newId, 'by' => $this->currentUserName()]);
        $this->redirect([], __('Lid is aangemaakt!', 'rcman'));
    }

    public function handleDelete(): void
    {
        $id = absint($_GET['id'] ?? 0); // phpcs:ignore WordPress.Security.NonceVerification
        $this->authorize('rcman_delete_member_' . $id, Capabilities::DELETE_MEMBERS);

        $member = $this->members->find($id);

        if (!$member) {
            $this->redirect([], null, __('Lid niet gevonden.', 'rcman'));
        }

        $this->members->delete($id);
        Logger::info('Lid verwijderd', ['name' => $member->name, 'by' => $this->currentUserName()]);

        $this->redirect([], __('Lid verwijderd!', 'rcman'));
    }
}
