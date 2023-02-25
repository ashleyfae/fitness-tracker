export default () => ({
    loaded: false,
    routines: [],
    error: null,

    init() {
        axios.get( route( 'routines.index' ) )
            .then( res => {
                this.routines = res.data.data;
                this.loaded = true;

                console.log(res.data.data);
            } )
            .catch(error => {
                this.error = error.message;
            })
    }
})
