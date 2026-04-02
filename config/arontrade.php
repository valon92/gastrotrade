<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inbox për «Dërgo në Email Zyrtar» (/konfirmo-porosine)
    |--------------------------------------------------------------------------
    |
    | Porositë dërgohen te kjo adresë nëse MAIL_ORDER_INBOX nuk është vendosur.
    | MAIL_FROM_ADDRESS mund të jetë e njëjta (Gmail SMTP) ose ndryshe kur
    | përdorni shërbim tjetër dërgimi.
    |
    */

    'order_inbox' => env('MAIL_ORDER_INBOX') ?: env('OFFICIAL_ORDER_EMAIL', 'svalon95@gmail.com'),

];
