document.addEventListener('DOMContentLoaded', function () {
    new Vue({
        el: '#production-data-entries-app',
        name: 'ProductionDataEntries',
        delimiters: ['[[', ']]'],
        data: {
            entries: [],
            
            crops: {
                'potatoes': 'Potatoes',
                'tomatoes': 'Tomatoes',
                'salad': 'Salad',
                'onions': 'Onions',
                'radishes': 'Radishes',
                'cucumber': 'Cucumber',
                'cauliflower': 'Cauliflower',
            }
        },
        filters: {
            formatType: function (val) {
                return {
                    'planting_seeding': 'Planting/Seeding',
                    'harvesting': 'Harvesting',
                    'fertilizing': 'Fertilizing',
                    'watering': 'Watering'
                }[val];
            }
        },
        methods: {
            addEntry: function (type) {
                const entry = {type: type, area: 0};
                if (type === 'planting_seeding') {
                    entry.crop = '';
                } else {
                    entry.relatedEntry = null;
                }
                if (type === 'fertilizing') {
                    entry.fertilizerType = '';
                }
                this.entries.push(entry);
            }
        }
    })
})