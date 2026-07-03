<?php
defined('ABSPATH') || exit;

/**
 * Custom user roles for the portal: customer, dealer, partner, employee.
 * (guest = unauthenticated, admin = WP administrator.)
 */
class DTC_Roles
{
    public const ROLES = [
        'dtc_customer' => 'Customer',
        'dtc_dealer' => 'Dealer',
        'dtc_partner' => 'Partner',
        'dtc_employee' => 'Employee',
    ];

    public static function add_roles(): void
    {
        foreach (self::ROLES as $slug => $label) {
            add_role($slug, $label, [
                'read' => true,
                'dtc_portal_access' => true,
                'dtc_download_files' => true,
                'dtc_create_tickets' => true,
            ]);
        }

        $admin = get_role('administrator');
        if ($admin) {
            foreach (['dtc_portal_access', 'dtc_download_files', 'dtc_create_tickets', 'dtc_manage'] as $cap) {
                $admin->add_cap($cap);
            }
        }
    }

    public static function is_portal_user(WP_User $user): bool
    {
        return $user->has_cap('dtc_portal_access');
    }
}
