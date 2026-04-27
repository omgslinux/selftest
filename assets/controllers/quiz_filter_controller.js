import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['topic'];

    connect() {
        this.categoryField = document.getElementById('category');
        this.topicField = document.getElementById('topic');
        
        if (this.categoryField) {
            this.categoryField.addEventListener('change', this.onCategoryChange.bind(this));
        }
    }

    onCategoryChange(event) {
        const categoryId = event.target.value;
        
        this.topicField.innerHTML = '<option value="">Todos los temas</option>';
        
        if (!categoryId) {
            this.topicField.disabled = true;
            return;
        }
        
        this.topicField.disabled = false;
        
        fetch(`/api/topics?category=${categoryId}`)
            .then(response => response.json())
            .then(topics => {
                topics.forEach(topic => {
                    const option = document.createElement('option');
                    option.value = topic.id;
                    option.textContent = topic.name;
                    this.topicField.appendChild(option);
                });
            });
    }
}