<template>
    <div>
        <ul id="example-1">
          <li v-for="item in raids">
            {{ item.slug }}
          </li>
        </ul>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                items: [
                  { message: 'Foo' },
                  { message: 'Bar' }
              ],
              raids: JSON.parse( localStorage.getItem('pokematos_raids'))
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        created() {
            this.getRaids()
        },
        methods: {
            getRaids() {
                axios.get('/api/user/cities').then( res => {
                    this.raids = res.data
                    console.log(res.data)
                    localStorage.setItem('pokematos_raids', JSON.stringify(res.data));
                }).catch( err => {
                    //No error
                });
            }
        }
    }
</script>
