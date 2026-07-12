        const basePrice = $destination['prix'] ;
        let adults = 1;
        let children = 0;

        function updateCounter(type, change) {
            if (type === 'adults') {
                adults = Math.max(1, adults + change);
                document.getElementById('adults-count').textContent = adults;
            } else if (type === 'children') {
                children = Math.max(0, children + change);
                document.getElementById('children-count').textContent = children;
            }
            calculateTotal();
        }

        function calculateTotal() {
            const adultPrice = basePrice;
            const childPrice = basePrice * 0.8;
            const total = (adults * adultPrice) + (children * childPrice);

            document.getElementById('total-price').textContent = total.toFixed(2) + ' $';
            document.getElementById('booking-total').textContent = total.toFixed(2) + ' $';
        }