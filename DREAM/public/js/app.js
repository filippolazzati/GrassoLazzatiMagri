document.addEventListener('DOMContentLoaded', function () {
    initTableRowHref()
})

function initTableRowHref() {
    document.querySelectorAll('table tr[data-href]').forEach(function ($tr) {
        $tr.addEventListener('click', function () {
            window.location.href = $tr.dataset.href
        })

        $tr.addEventListener('auxclick', function () {
            window.open($tr.dataset.href, '_blank')
        })
    })
}