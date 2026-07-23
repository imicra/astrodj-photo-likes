document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.photo-like').forEach(button => {

        button.addEventListener('click', async function () {

            button.classList.add('likes-animation');

            setTimeout(function(){
                button.classList.remove('likes-animation');
            }, 300);

            // Уже поставил лайк
            if (button.classList.contains('liked'))
                return;

            // Защита от двойного клика
            if (button.classList.contains('loading'))
                return;

            button.classList.add('loading');

            const form = new FormData();

            form.append('action', 'photo_like');
            form.append('photo_id', button.dataset.photo);
            form.append('nonce', PhotoLikes.nonce);

            try {

                const response = await fetch(PhotoLikes.ajax, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: form
                });

                const json = await response.json();

                button.classList.remove('loading');

                if (!json.success) {

                    // если уже голосовал
                    if (json.data.message === 'already') {

                        button.classList.add('liked');
                    }

                    return;
                }

                button.classList.add('liked');

                button.querySelector('.count').textContent = json.data.likes;
                button.querySelector('.count').classList.add('visible');

            }
            catch (e) {

                button.classList.remove('loading');

                console.error(e);

            }

        });

    });

});