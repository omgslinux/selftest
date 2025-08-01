// assets/controllers/form-collection_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["collectionContainer"]

    static values = {
        index    : Number,
        prototype: String,
    }

    connect() {
        // Añade botones de eliminar a los ítems existentes al cargar
        document.querySelectorAll('.data-collection-item').forEach(item => {
            this.addDeleteButton(item);
        });
    }

    addCollectionElement(event)
    {
        const item = document.createElement('li');
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;

        this.addDeleteButton(item);
    }

    addDeleteButton(item)
    {
        const removeFormButton = document.createElement('button');
        removeFormButton.innerText = 'Delete this item';
        removeFormButton.classList.add('btn','btn-danger');

        item.append(removeFormButton);

        removeFormButton.addEventListener('click', (e) => {
            e.preventDefault();
            // remove the li for the tag form
            item.remove();
        });
    }
}
