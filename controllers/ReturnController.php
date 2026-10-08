<?php
declare(strict_types=1);

class ReturnController
{
    public function handle(): void
    {
        $user = require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            render('return', ['loans' => Loan::activeByUser($user->getId(), db())]);
            return;
        }
        verify_csrf();
        $loanId = (int)($_POST['loan_id'] ?? 0);
        if ($loanId > 0 && Loan::giveBack($loanId, $user->getId(), db())) {
            flash('success', '返却が完了しました。');
            redirect('history');
        }
        flash('error', '返却できませんでした。');
        redirect('return');
    }
}
