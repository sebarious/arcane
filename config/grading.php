<?php

return [

    // Grading companies offered when marking a card as graded. Stored as
    // free text in card_inventory.graded_by (PulseAPI writes that column too,
    // and isn't limited to this list), so adding or removing an entry here
    // only changes what the dropdown offers — it never invalidates a card
    // that's already been graded by someone else.
    'companies' => [
        'PSA' => 'PSA',
        'BGS' => 'Beckett (BGS)',
        'CGC' => 'CGC',
        'SGC' => 'SGC',
        'ACE' => 'ACE',
        'TAG' => 'TAG',
        'Other' => 'Other',
    ],

];
