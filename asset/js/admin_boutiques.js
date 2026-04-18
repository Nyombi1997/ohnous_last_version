(function($){

    function initSearchSuggestions(inputId, listId, suggestions){
        const input = document.getElementById(inputId);
        const list = document.getElementById(listId);

        if(!input || !list || !Array.isArray(suggestions)){
            return;
        }

        function render(query){
            const value = (query || '').trim().toLowerCase();
            if(value === ''){
                list.innerHTML = '';
                list.classList.remove('is-visible');
                return;
            }

            const matches = suggestions.filter(function(item){
                return (item.label || '').toLowerCase().includes(value)
                    || (item.email || '').toLowerCase().includes(value)
                    || (item.slug || '').toLowerCase().includes(value);
            }).slice(0, 6);

            if(matches.length === 0){
                list.innerHTML = '';
                list.classList.remove('is-visible');
                return;
            }

            list.innerHTML = matches.map(function(item){
                const extra = item.email || item.slug || '';
                return `<a href="${item.url}" class="admin-search-suggestions__item"><strong>${item.label}</strong><span>${extra}</span></a>`;
            }).join('');
            list.classList.add('is-visible');
        }

        input.addEventListener('input', function(){
            render(input.value);
        });

        input.addEventListener('focus', function(){
            render(input.value);
        });

        document.addEventListener('click', function(event){
            if(!list.contains(event.target) && event.target !== input){
                list.classList.remove('is-visible');
            }
        });
    }

    function toggleStore(storeId, activate, button){
        const tempText = button.innerHTML;
        button.setAttribute('disabled', '');
        button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';

        $.post('/fonctions/admin_store_actions.php', {
            action: 'toggle_store',
            store_id: storeId,
            activate: activate
        }, function(data){
            Swal.fire({
                icon: data.result === 'ok' ? 'success' : 'error',
                title: data.msg,
                confirmButtonColor: '#6775d6'
            }).then(function(){
                if(data.result === 'ok'){
                    window.location.reload();
                }
            });
        }, 'json').always(function(){
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
        });
    }

    $(document).on('click', '.admin-toggle-store', function(){
        const button = this;
        if(button.getAttribute('data-is-test') === '1' && button.getAttribute('data-next-state') === '0'){
            Swal.fire({
                icon: 'info',
                title: 'Une boutique test reste active tant qu’elle n’a pas d’adresse email.',
                confirmButtonColor: '#6775d6'
            });
            return;
        }
        const storeId = button.getAttribute('data-store-id');
        const activate = button.getAttribute('data-next-state');
        toggleStore(storeId, activate, button);
    });

    const messageForm = document.getElementById('admin_store_message_form');
    if(messageForm){
        messageForm.addEventListener('submit', function(e){
            e.preventDefault();

            const thread = document.getElementById('admin_store_thread');
            const storeId = thread.getAttribute('data-store-id');
            const textarea = document.getElementById('admin_store_message_text');
            const message = textarea.value.trim();

            if(message === ''){
                Swal.fire({
                    icon: 'error',
                    title: 'Écrivez un message avant l’envoi.',
                    confirmButtonColor: '#6775d6'
                });
                return;
            }

            const button = messageForm.querySelector('button[type="submit"]');
            const tempText = button.innerHTML;
            button.setAttribute('disabled', '');
            button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';

            $.post('/fonctions/admin_store_actions.php', {
                action: 'send_message',
                store_id: storeId,
                message: message
            }, function(data){
                if(data.result !== 'ok'){
                    Swal.fire({
                        icon: 'error',
                        title: data.msg || 'Impossible d’envoyer le message.',
                        confirmButtonColor: '#6775d6'
                    });
                    return;
                }

                textarea.value = '';
                thread.innerHTML = data.thread_html;
            }, 'json').always(function(){
                button.removeAttribute('disabled');
                button.innerHTML = tempText;
            });
        });
    }

    function slugify(value){
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .replace(/-+/g, '-');
    }

    const adminTestStoreForm = document.getElementById('admin_test_store_form');
    if(adminTestStoreForm){
        const storeId = adminTestStoreForm.getAttribute('data-store-id');
        const nameInput = document.getElementById('admin_test_store_name');
        const slugInput = document.getElementById('admin_test_store_slug');
        const descriptionInput = document.getElementById('admin_test_store_description');

        if(nameInput && slugInput){
            const syncSlugPreview = function(){
                slugInput.value = slugify(nameInput.value || '') || 'boutique';
            };

            nameInput.addEventListener('input', syncSlugPreview);
            syncSlugPreview();
        }

        adminTestStoreForm.addEventListener('submit', function(e){
            e.preventDefault();

            const button = adminTestStoreForm.querySelector('button[type="submit"]');
            const tempText = button.innerHTML;
            button.setAttribute('disabled', '');
            button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';

            $.post('/fonctions/admin_store_actions.php', {
                action: 'update_test_store',
                store_id: storeId,
                nom: nameInput ? nameInput.value.trim() : '',
                description: descriptionInput ? descriptionInput.value.trim() : ''
            }, function(data){
                if(data.result !== 'ok'){
                    Swal.fire({
                        icon: 'error',
                        title: data.msg || 'Impossible de mettre à jour la boutique.',
                        confirmButtonColor: '#6775d6'
                    });
                    return;
                }

                if(slugInput && data.slug){
                    slugInput.value = data.slug;
                }

                Swal.fire({
                    icon: 'success',
                    title: data.msg,
                    confirmButtonColor: '#6775d6'
                }).then(function(){
                    window.location.reload();
                });
            }, 'json').always(function(){
                button.removeAttribute('disabled');
                button.innerHTML = tempText;
            });
        });
    }

    initSearchSuggestions('admin_store_search_input', 'admin_store_search_suggestions', window.adminStoreSuggestions || []);
    initSearchSuggestions('admin_article_search_input', 'admin_article_search_suggestions', window.adminArticleSuggestions || []);
})(jQuery);
