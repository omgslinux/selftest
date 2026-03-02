import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['topic'];

    connect() {
        this.categoryField = document.querySelector('[id$="_category"]');
        this.topicField = document.querySelector('[id$="_topic"]');
        
        if (this.categoryField && this.topicField) {
            this.categoryField.addEventListener('change', this.onCategoryChange.bind(this));
        }
    }

    onCategoryChange(event) {
        const categoryId = event.target.value;
        
        // If no category selected, don't filter
        if (!categoryId) {
            this.topicField.closest('.form-group').style.display = '';
            return;
        }

        // Get all topic options
        const topicOptions = this.topicField.querySelectorAll('option');
        
        topicOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = '';
                return;
            }
            
            // We'll filter via AJAX
            option.style.display = 'none';
        });

        // Fetch filtered topics
        fetch(`/api/quiz/topics?category=${categoryId}`)
            .then(response => response.json())
            .then(topics => {
                // Clear current options except the empty one
                const firstOption = this.topicField.querySelector('option');
                this.topicField.innerHTML = '';
                if (firstOption && firstOption.value === '') {
                    this.topicField.appendChild(firstOption);
                }
                
                // Add filtered topics
                topics.forEach(topic => {
                    const option = document.createElement('option');
                    option.value = topic.id;
                    option.textContent = topic.name;
                    this.topicField.appendChild(option);
                });
            });
    }
}
