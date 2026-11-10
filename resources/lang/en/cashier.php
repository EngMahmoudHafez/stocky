<?php

return [
    'drawer' => 'Choose a cashier drawer account assigned to this cashier.',
    'already_open' => 'The cashier or account already has an open shift. Close it first.',
    'opening_mismatch' => 'Opening cash must match the account balance. Fund the drawer before opening the shift.',
    'open_first' => 'Open a shift linked to your cashier account before receiving cash. Close any older unlinked shift first.',
    'account_mismatch' => 'Cash payments must go to the account linked to the current shift.',
    'payment_account' => 'Choose a collection account for this payment.',
    'noncash_drawer' => 'Card and other non-cash payments cannot be posted to a cashier drawer.',
    'closed' => 'The shift is closed or has no linked account.',
    'receiver' => 'Choose a different active treasury recipient authorized for this branch.',
    'different_accounts' => 'The source and destination accounts must be different.',
    'treasury' => 'Choose an active treasury destination account.',
    'insufficient' => 'The amount exceeds the cash available in the shift or account.',
    'duplicate_mismatch' => 'This receipt key was already used for a different handover. Reload before starting a new handover.',
    'immutable' => 'Cashier handover receipts cannot be edited or deleted from general transfers.',
    'linked_payment' => 'This payment belongs to a shift and cannot be edited or deleted directly. Record a return document for cash refunds.',
    'active_account' => 'Use the cashier treasury screen for movements on an open drawer. Close the shift before reassigning or deleting its account.',
];
