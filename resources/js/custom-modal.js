const bt_cerrar_modal = document.getElementById('bt_cerrar_modal');
bt_cerrar_modal.addEventListener('click', function () {
    // console.log('cerrar_modal');
    document.getElementById('myModal').close();
});

const bt_abrir_modal = document.getElementById('bt_abrir_modal');
bt_abrir_modal.addEventListener('click', function () {
    // console.log('abrir_modal');
    document.getElementById('myModal').showModal();
});


let bt_save_object = document.getElementById('bt_save_object');
