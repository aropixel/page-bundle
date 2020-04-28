function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

function addCKEditorInLastNode(tabsNode) {
    const ckEditorsInput = tabsNode.querySelectorAll('.ckeditor');
    const lastckEditorsInput = ckEditorsInput[ckEditorsInput.length - 1];

    CKEDITOR.replace(lastckEditorsInput)
}

function addBlockTabOnClick() {
    const collectionWrapperNode = document.querySelector('.js-block-admin-tabs-collection');

    collectionWrapperNode.querySelector('.js-block-admin-tabs-add').addEventListener('click', function(e) {
        e.preventDefault();

        // Get the data-prototype explained earlier
        const prototype = collectionWrapperNode.dataset.prototype;

        // get the new index
        const index = collectionWrapperNode.dataset.index;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        const newFormContent = prototype.replace(/__name__/g, index);
        let newFormNode = document.createElement('div');

        const tabsNode = collectionWrapperNode.querySelector('.js-block-admin-tabs');

        // increase the index with one for the next item
        collectionWrapperNode.dataset.index = index + 1;

        // Display the form in the page before the "new" link
        tabsNode.innerHTML += newFormContent;

        // initialize le ck editor sur les champs textarea avec la classe ckeditor
        addCKEditorInLastNode(tabsNode);

        // récupère tous les liens de remove
        const removeTabLinks = document.querySelectorAll('.js-block-admin-tab-remove');

        // recupère le dernier lien remove, soit celui qui vient d'être ajouté
        const removeTabLinksLast = removeTabLinks[removeTabLinks.length - 1];

        // ajoute l'event listener du remove sur ce lien
        removeOneBlockTabOnClick(removeTabLinksLast);

    });
}

function removeOneBlockTabOnClick(element) {
    element.addEventListener('click', function(e) {
        e.preventDefault();
        this.closest('.js-block-admin-tab').remove();
    });
}

function removeBlockTabOnClick() {
    const removeTabLinks = document.querySelectorAll('.js-block-admin-tab-remove');
    removeTabLinks.forEach(function (element) {
        removeOneBlockTabOnClick(element);
    });

}

docReady(() => {
    addBlockTabOnClick();
    removeBlockTabOnClick();
});
