<template>
    <v-card class="permission">
        <v-card-title v-if="open" primary-title>
            <div>

                <p>Les joueurs ayant le(s) role(s)</p>
                <select multiple v-if="roles" v-model="permission.roles">
                    <option v-for="role in roles" :value="role.id">{{role.name}}</option>
                </select>
                <p>Peuvent publier des messages avec des @mentions de roles de cette catégorie dans les salons</p>
                <select multiple v-if="channels" v-model="permission.channels">
                    <option v-for="channel in channels" :value="channel.id">{{channel.name}}</option>
                </select>
                <select v-model="permission.type" class="select-permission-type">
                    <option value="auth">peuvent publier </option>
                    <option value="block">ne peuvent pas publier</option>
                </select>
            </div>
        </v-card-title>
        <v-card-title v-else primary-title>
            <div class="permission__content">
                <p v-html="message"></p>
            </div>
            <div class="permission__actions">            
                <v-btn v-if="open" flat @click="open = false">Valider</v-btn>
                <v-btn v-if="!open" icon flat dark @click="open = true"><v-icon>edit</v-icon></v-btn>
                <v-btn icon flat dark @click="$emit('delete-permission', permission)"><v-icon>delete</v-icon></v-btn>
            </div>
        </v-card-title>
        <v-card-actions v-if="open">
            <v-btn icon flat dark @click="$emit('delete-permission', permission)"><v-icon>delete</v-icon></v-btn>
            <v-spacer></v-spacer>
            <v-btn v-if="open" flat @click="open = false">Valider</v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
    export default {
        name: 'AdminPermission',
        props: {
            permission: {
                type: Object,
                required: true,
            },
            channels: {
                type: Array,
                required: true,
            },
            roles: {
                type: Array,
                required: true,
            },
        },
        data() {
            return {
                open:true,
            }
        },
        created() {
            console.log('ttt');
            if( this.permission.id ) this.open = false;
        },
        computed: {
            message() {
                if( this.roles.length == 0 ) return '';
                if( this.channels.length == 0 ) return '';
                let that = this;
                let debut = ( this.permission.type == 'auth' ) ? 'Les utilisateurs ayant' : 'Tous les utilisants, sauf ceux ayant' ;
                let roles = ( this.permission.roles.length > 1 ) ? ' les roles ' : ' le role ';
                if( this.permission.roles ) {
                    this.permission.roles.forEach(function(id, index) {
                        let role = that.roles.find( el => el.id == id );
                        let num = index+1;
                        roles += '<span class="permission__role">@'+role.name+'</span>';
                        if( num < that.permission.roles.length ) {
                            roles += ( num == that.permission.roles.length - 1 ) ? ' ou ' : ', ' ;
                        }
                    });
                }
                let action = ' peuvent publier dans';
                let channels = ( this.permission.roles.length > 1 ) ? ' les salons ' : ' le salon ';
                this.permission.channels.forEach(function(id, index) {
                    let channel = that.channels.find( el => el.id == id );
                    let num = index+1;
                    channels += '<span class="permission__role">#'+channel.name+'</span>';
                    if( num < that.permission.channels.length ) {
                        channels += ( num == that.permission.channels.length - 1 ) ? ' et ' : ', ' ;
                    }
                });
                let message = debut + roles + action + channels;
                return message;
            }
        }
    }
</script>
