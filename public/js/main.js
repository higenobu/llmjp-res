// Ask for confirmation before loaning a book.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.borrow-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!window.confirm('この書籍を貸し出しますか？')) {
                e.preventDefault();
            }
        });
    });
});
