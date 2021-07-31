<template>
    <div class="setting">
        <label>Étapes du Pokétrain</label>
        <draggable v-model="editableSteps" group="people" @start="drag=true" @end="drag=false" handle=".step__drag">

        <transition-group type="transition" name="flip-list">
        <div :class="'step step_opened_'+step.opened" v-for="(step, index) in editableSteps" :key="step.key">
            <div class="step__drag"><v-btn small flat fab><v-icon>reorder</v-icon></v-btn></div>
            <div v-if="step.opened" class="step__content">
                <select v-model="step.type">
                    <option v-for="stepType in stepTypes" :value="stepType.id" :key="stepType.id">{{stepType.name}}</option>
                </select>
                <div v-if="step.type == 'stop' && gyms">
                    <multiselect v-model="step.stop" track-by="id" label="name" placeholder="Choisir une arène" :options="gyms" :searchable="true" :allow-empty="false">
                      <template slot="singleLabel" slot-scope="{ option }"><span v-if="option.ex">[EX] </span><span v-if="option.zone">{{ option.zone.name }} - </span>{{ option.name }}</template>
                      <template slot="option" slot-scope="props"><span v-if="props.option.ex">[EX] </span><span v-if="props.option.zone">{{ props.option.zone.name }} - </span>{{ props.option.name }}</template>
                    </multiselect>
                </div>
                <input v-model="step.description" type="text" placeholder="Description...">
                <div class="milestone">
                    <v-checkbox
                        v-model="step.milestone"
                        label="Étape avec heure de RDV"
                        :value="step.milestone">
                    </v-checkbox>
                    <v-layout v-if="step.milestone">
                        <v-flex xs6>
                            <select dir="rtl" class="hour" v-if="exAllowedHours" v-model="step.hour">
                                <option v-for="hour in exAllowedHours" :value="hour" :key="hour">{{hour}}h</option>
                            </select>
                        </v-flex>
                        <v-flex xs6>
                            <select class="minutes" v-if="exAllowedMinutes" v-model="step.minutes">
                                <option v-for="minutes in exAllowedMinutes" :value="minutes" :key="minutes">{{minutes}}</option>
                            </select>
                        </v-flex>
                    </v-layout>
                </div>
                <div class="invitation__action">
                    <v-layout wrap align-content-center align-center justify-center>
                        <v-flex xs6>
                            <v-btn round large class="accept" @click="step.opened = false"><v-icon>check</v-icon>&nbsp;Valider</v-btn>
                        </v-flex>
                        <v-flex xs6>
                            <v-btn round large class="refuse" @click="removeStep(index)"><v-icon>close</v-icon>&nbsp;Supprimer</v-btn>
                        </v-flex>
                    </v-layout>
                </div>
            </div>
            <div v-if="!step.opened" class="step__content closed">
                <div class="step__time" v-if="step.milestone">
                    {{step.hour}}h{{step.minutes}}
                </div>
                <div class="stop__marker">
                    <img v-if="step.type == 'stop' && step.stop && step.stop.ex" :src="baseUrl+'/storage/img/static/connector_gym_ex.png'">
                    <img v-if="step.type == 'stop' && step.stop && !step.stop.ex" :src="baseUrl+'/storage/img/static/connector_gym.png'">
                    <v-icon v-if="step.type != 'stop'">directions_car</v-icon>
                </div>
                <div>
                    <strong v-if="step.type == 'stop' && step.stop">
                        <span v-if="step.stop && step.stop.zone">{{step.stop.zone.name}} - </span>
                        {{step.stop.name}}
                    </strong>
                    <strong v-if="step.type == 'transport'">Trajet en voiture/bus</strong>
                    <div v-if="step.description" class="caption">{{step.description}}</div>
                </div>
            </div>
            <div class="step__actions">
                <v-btn v-if="step.opened" small flat fab @click="removeStep(index)"><v-icon>close</v-icon></v-btn>
                <v-btn v-if="step.opened" small flat fab @click="step.opened = false"><v-icon>check</v-icon></v-btn>
                <v-btn v-if="!step.opened" small flat fab @click="step.opened = true"><v-icon>edit</v-icon></v-btn>
            </div>
            <v-btn class="add_step_middle" small flat fab @click="addStep(index)"><v-icon>add</v-icon></v-btn>
        </div>
        </transition-group>

        </draggable>
        <v-btn style="margin-top: 20px" class="secondary"round large @click="addStep(editableSteps.length)"><v-icon>add</v-icon>Ajouter une étape</v-btn>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'
    import draggable from 'vuedraggable'
    export default {
        name: 'EventTrain',
        components: { Multiselect, draggable },
        props: ['steps'],
        data() {
            return {
                editableSteps: this.steps,
                numKeyStep: 0,
                exAllowedHours: [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
                exAllowedMinutes: [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55],
                stepTypes: {
                    'stop': {
                        id: 'stop',
                        name: 'Arène'
                    },
                    'transport': {
                        id: 'transport',
                        name: 'Trajet en voiture/bus'
                    }
                },
            }
        },
        created() {
        },
        computed: {
            gyms() {
                return this.$store.state.gyms.filter( gym => gym.gym);
            },
            baseUrl() {
                return window.pokematos.baseUrl;
            },
        },
        methods: {
            addStep( index ) {
                console.log(index)
                if( typeof this.editableSteps == "undefined" ) this.editableSteps = [];
                this.numKeyStep++;
                let toAdd = {id:null,name:'', type:'stop', opened: true, key: this.numKeyStep};
                this.editableSteps.splice(index, 0, toAdd);
                //this.steps.push({id:null,name:'', type:'stop', opened: true, key: this.numKeyStep});
            },
            removeStep(index) {
                this.editableSteps.splice(index, 1);
            },
        }
    }
</script>
