const searchBar = document.getElementById('searchBar');
const searchBarOptions = document.getElementById('searchBarOptions');

let lastSearch = "";

function observeSearch() {
    const searchString = searchBar.value.toLowerCase();
    // detect if searchstring start wiht ###
    if (lastSearch == searchString) return
    if (searchString.startsWith("###")) {
        console.log("START GOOD")
        // get id of projet
        let idProjet = searchString.split("###")[1];
        // redirect to projet
        window.location.href = "?action=projet&id=" + idProjet;
    }

    let options = searchBarOptions.getElementsByTagName('option');
    if (searchString.length > 2) {
        url = "api?action=searchProjet&s=" + searchString;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                displayOptions(data);
            })
            .catch(err => console.error(err));
    }
}
// detect when searchBar is active

searchBar.addEventListener("focus", () => {
    observeSearch();
    setInterval(observeSearch, 250);
});

searchBar.addEventListener("keydown", (e) => {
    if (e.key == "Enter") {
        console.log("ENTER")
        // get id of projet
        let searchString = searchBar.value.toLowerCase();
        // redirect to projet
        window.location.href = "?action=recher&s=" + searchString;
    }

})

searchBar.addEventListener("focusout",()=>{
    clearInterval(observeSearch);
})

const displayOptions = (options) => {
    const htmlString = options
        .map((option) => {
            return `
            <option href="${option._url}" class="option" value="###${option._idProjet}" >
                <span >${option._titre}</span>
            </option>
        `;
        })
        .join('');
    searchBarOptions.innerHTML = htmlString;
}


searchBar.addEventListener("change", (e) => {
    console.log("change")

})