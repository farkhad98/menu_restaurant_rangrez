document.querySelectorAll( 'textarea' ).forEach(element => {
    ClassicEditor
        .create(element)
        .then( editor => {
            console.log( editor );
        } )
        .catch( error => {
            console.error( error );
        } );
})
