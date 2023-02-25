import getClosest from "../helpers/get-closest";

const modalTriggers = document.querySelectorAll( '.modal-trigger' );
if ( modalTriggers ) {
    modalTriggers.forEach( ( trigger ) => {
        trigger.addEventListener( 'click', ( event ) => {
            event.preventDefault();

            const modalId = trigger.getAttribute( 'data-target' );
            const modal = modalId ? document.getElementById( modalId ) : false;
            if ( ! modal ) {
                return;
            }

            modal.classList.add( 'is-active' );

            const modalContent = modal.querySelector( 'input' );
            if ( modalContent ) {
                modalContent.focus();
            }

            document.documentElement.classList.add( 'is-clipped' );
        } );
    } );
}

export function closeModal( modal ) {
    modal.classList.remove( 'is-active' );
    document.documentElement.classList.remove( 'is-clipped' );
}


const modalClosers = document.querySelectorAll( '.modal-close' );
if ( modalClosers ) {
    modalClosers.forEach( ( closer ) => {
        closer.addEventListener( 'click', ( event ) => {
            event.preventDefault();

            const modal = getClosest( closer, '.modal' );
            if ( modal ) {
                closeModal( modal );
            }
        } );
    } );
}

const modalBackgrounds = document.querySelectorAll( '.modal-background' );
if ( modalBackgrounds ) {
    modalBackgrounds.forEach( ( background ) => {
        background.addEventListener( 'click', ( event ) => {
            const modal = getClosest( background, '.modal' );
            if ( modal ) {
                closeModal( modal );
            }
        } )
    } )
}

document.addEventListener( 'keyup', ( event ) => {
    if ( event.key === 'Escape' ) {
        const modals = document.querySelectorAll( '.modal.is-active' );
        if ( modals ) {
            modals.forEach( ( modal ) => {
                modal.classList.remove( 'is-active' );
            } );
        }
    }
} )
