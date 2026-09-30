<?php

return [

    // Charged on top of a wallet withdrawal, deducted from the wallet
    // balance alongside the payout itself — a £100 withdrawal at 3% debits
    // £103 from the balance, and the customer still receives £100.
    // See RipWalletService::requestWithdrawal().
    'withdrawal_fee_rate' => (float) env('RIPS_WITHDRAWAL_FEE_RATE', 0.03),

];
