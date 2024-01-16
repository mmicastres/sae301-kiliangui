let edit_buttons = document.querySelectorAll('.edit-button');

edit_buttons.forEach(function (button) {
    console.log(button);
    button.addEventListener('click', function (event) {
        let idCommentaire = event.target.value
        let commentaire = document.getElementById(idCommentaire)
        let modify_form = commentaire.querySelector('.modify-form');
        console.log(modify_form)
        modify_form.classList.toggle('d-none');
        let comment = commentaire.querySelector('.comment');
        comment.classList.toggle('d-none');
        // disable the display of the modify button
        event.target.parentNode.classList.toggle("d-none")
    });
}
);