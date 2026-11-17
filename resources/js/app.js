window.isiAlasan = function (form) {
    const alasan = window.prompt('Kasih tau alasan nolak jadwal ini (biar siswa tau kenapa):');

    if (alasan === null) {
        return false;
    }

    form.querySelector('input[name="alasan"]').value = alasan;

    return true;
};
