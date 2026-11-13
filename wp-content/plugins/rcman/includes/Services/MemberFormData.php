<?php

namespace RCMan\Services;

use RCMan\Enums\ClubRole;
use RCMan\Enums\InstructorType;
use RCMan\Support\Request;

defined('ABSPATH') || exit;

/**
 * Reads and validates member form input, shared by the admin form and the public sign-up form.
 */
final class MemberFormData
{
    /** @var array<string, mixed> */
    public array $data = [];

    /** @var string[] */
    public array $roles = [];

    /** @var int[] */
    public array $licenceIds = [];

    /** @var string[] */
    public array $instructorTypes = [];

    /** @var string[] */
    public array $errors = [];

    /**
     * @param bool $public Public sign-up: all personal fields required, no roles/instructor input.
     */
    public static function fromRequest(bool $public = false): self
    {
        $form = new self();

        $form->data = [
            'name'         => mb_substr(Request::text('name'), 0, 191),
            'email'        => Request::email('email'),
            'birthdate'    => Request::date('birthdate'),
            'address'      => mb_substr(Request::text('address'), 0, 191),
            'zip_code'     => strtoupper(str_replace(' ', '', mb_substr(Request::text('zip_code'), 0, 20))),
            'city'         => mb_substr(Request::text('city'), 0, 100),
            'mobile_phone' => mb_substr(Request::text('mobile_phone'), 0, 30),
            'knvvl'        => mb_substr(Request::text('knvvl'), 0, 50),
            'rdw_number'   => mb_substr(Request::text('rdw_number'), 0, 50),
        ];

        $form->licenceIds = array_values(array_filter(array_map('intval', Request::array('licences'))));

        if (!$public) {
            $form->data['in_memoriam'] = Request::bool('in_memoriam') ? 1 : 0;
            $form->roles               = ClubRole::sanitize(Request::array('roles'));
            $form->instructorTypes     = InstructorType::sanitize(Request::array('instructor'));
        }

        $form->validate($public);

        return $form;
    }

    private function validate(bool $public): void
    {
        if ($this->data['name'] === '') {
            $this->errors[] = __('Naam is verplicht.', 'rcman');
        }

        if (!is_email($this->data['email'])) {
            $this->errors[] = __('Vul een geldig e-mailadres in.', 'rcman');
        }

        if (Request::text('birthdate') !== '' && $this->data['birthdate'] === null) {
            $this->errors[] = __('Geboortedatum is ongeldig.', 'rcman');
        }

        if ($public) {
            $required = [
                'birthdate'    => __('Geboortedatum', 'rcman'),
                'address'      => __('Adres', 'rcman'),
                'zip_code'     => __('Postcode', 'rcman'),
                'city'         => __('Woonplaats', 'rcman'),
                'mobile_phone' => __('Telefoonnummer', 'rcman'),
            ];

            foreach ($required as $field => $label) {
                if (empty($this->data[$field])) {
                    /* translators: %s: field label */
                    $this->errors[] = sprintf(__('%s is verplicht.', 'rcman'), $label);
                }
            }
        } elseif (!$this->roles) {
            $this->errors[] = __('Kies minimaal één lidmaatschap.', 'rcman');
        }
    }

    public function isValid(): bool
    {
        return !$this->errors;
    }
}
