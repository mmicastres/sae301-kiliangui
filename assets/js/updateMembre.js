let modMembre = document.getElementById("modMembre")
let confModMembre = document.getElementById("confModMembre")
let infoMembre = document.getElementById("infoMembre")
modMembre.addEventListener('click', function (event) {
    event.preventDefault();
    let fields = infoMembre.querySelectorAll('.form-control');
    fields.forEach(function (field) {
        field.removeAttribute('disabled');
    });
    modMembre.classList.toggle('d-none');
    confModMembre.classList.toggle('d-none');

}
);