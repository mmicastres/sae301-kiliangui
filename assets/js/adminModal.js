const addCategorie = document.getElementById('addCategorie');
const CategorieModal = document.getElementById('CategorieModal'); // dialog element
const addContexte = document.getElementById('addContexte');
const ContexteModal = document.getElementById('ContexteModal');

const modCategorie = document.getElementsByClassName('modCategorie');
const modContexte = document.getElementsByClassName('modContexte');
const delCategorie = document.getElementById('delCategorie');
const delContexte = document.getElementById('delContexte');


function resetCategorieModal(action){
    const intitule = CategorieModal.querySelector('#intitule');
    intitule.value = '';
    const button = CategorieModal.querySelector('button');
    delCategorie.hidden = true;
    if (action === "add"){
        button.value = "Ajouter";
        button.innerText = "Ajouter";
        button.name = "addCategorie";
    }
    else if (action === "mod"){
        button.value = "Modifier";
        button.innerText = "Modifier";
        button.name = "modCategorie";
    }

}
function resetContexteModal(CTA){
    const intitule = ContexteModal.querySelector('#intitule');
    intitule.value = '';
    const semestre = ContexteModal.querySelector('#semestre');
    semestre.value = '';
    const Identifiant = ContexteModal.querySelector('#identifiant');
    Identifiant.value = '';
    const button = ContexteModal.querySelector('button');
    delContexte.hidden = true;
    if (CTA === "add"){
        button.value = "Ajouter";
        button.innerText = "Ajouter";
        button.name = "addContexte";
    }
    else if (CTA === "mod"){
        button.value = "Modifier";
        button.innerText = "Modifier";
        button.name = "modContexte";
    }

}
addCategorie.addEventListener('click', function() {
    resetCategorieModal("add");
    CategorieModal.showModal();
}
);
addContexte.addEventListener('click', function() {
    resetContexteModal("add");
    ContexteModal.showModal();
});


for (const categorie of modCategorie) {
    categorie.addEventListener('click', function() {
        resetCategorieModal("mod");

        const li = this.parentElement;
        const intitule = li.querySelector('span');
        const intitule_modal = CategorieModal.querySelector('#intitule');
        const idCategorie = CategorieModal.querySelector('#idCategorie');
        idCategorie.value = li.id;
        console.log(intitule_modal);
        console.log(intitule);
        intitule_modal.value = intitule.textContent;
        delCategorie.hidden = false;
        CategorieModal.showModal();
    });
}

for (const contexte of modContexte) {
    contexte.addEventListener('click', function() {
        resetContexteModal("mod");
        const li = this.parentElement;
        const intitule = li.querySelector('.intitule');
        const intitule_modal = ContexteModal.querySelector('#intitule');
        const semestre = li.querySelector('.semestre');
        const semestre_modal = ContexteModal.querySelector('#semestre');
        const identifiant = li.querySelector('.identifiant');
        const identifiant_modal = ContexteModal.querySelector('#identifiant');
        const idContexte = ContexteModal.querySelector('#idContexte');
        idContexte.value = li.id;
        intitule_modal.value = intitule.textContent;
        semestre_modal.value = semestre.textContent;
        identifiant_modal.value = identifiant.textContent;
        delContexte.hidden = false;
        ContexteModal.showModal();
    });
}