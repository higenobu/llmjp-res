<?php
declare(strict_types=1);

class HistoryController
{
    public function handle(): void
    {
        $user = require_login();
        render('history', ['loans' => Loan::historyByUser($user->getId(), db())]);
    }
}
