const getClosest = ( elem, selector ) => {
    for ( ; elem && elem !== document; elem = elem.parentNode ) {
        if ( elem.matches( selector ) ) {
            return elem;
        }
    }

    return null;
}

export default getClosest;
