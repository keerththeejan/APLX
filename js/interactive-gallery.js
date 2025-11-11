document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('mainImage');
    const imageDetails = document.getElementById('imageDetails');
    const areas = document.querySelectorAll('.area');
    
    // Set default image and details
    const defaultImage = mainImage.src;
    const defaultTitle = imageDetails.querySelector('h3').textContent;
    const defaultDesc = imageDetails.querySelector('p').textContent;
    
    // Add hover effect to each area
    areas.forEach(area => {
        const image = area.getAttribute('data-image');
        const title = area.getAttribute('data-title');
        const desc = area.getAttribute('data-desc');
        
        // Mouse enter event
        area.addEventListener('mouseenter', function() {
            // Change main image with fade effect
            mainImage.style.opacity = '0';
            
            setTimeout(() => {
                mainImage.src = image;
                mainImage.alt = title;
                mainImage.style.opacity = '1';
            }, 150);
            
            // Update details
            imageDetails.innerHTML = `
                <h3>${title}</h3>
                <p>${desc}</p>
            `;
            
            // Add active class to current area
            this.classList.add('active');
        });
        
        // Mouse leave event
        area.addEventListener('mouseleave', function() {
            // Reset to default after a delay if not hovering over another area
            setTimeout(() => {
                if (!area.matches(':hover')) {
                    resetToDefault();
                }
            }, 300);
            
            // Remove active class
            this.classList.remove('active');
        });
    });
    
    // Reset to default when mouse leaves the gallery
    document.querySelector('.interactive-gallery').addEventListener('mouseleave', function() {
        resetToDefault();
    });
    
    // Function to reset to default state
    function resetToDefault() {
        mainImage.style.opacity = '0';
        
        setTimeout(() => {
            mainImage.src = defaultImage;
            mainImage.alt = 'Main Service';
            mainImage.style.opacity = '1';
        }, 150);
        
        imageDetails.innerHTML = `
            <h3>${defaultTitle}</h3>
            <p>${defaultDesc}</p>
        `;
        
        // Remove active class from all areas
        areas.forEach(area => {
            area.classList.remove('active');
        });
    }
});
