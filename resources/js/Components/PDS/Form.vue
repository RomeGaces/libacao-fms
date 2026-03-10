<template>
    <form class="form-section">
        <div v-show="activeTab === 'personal'">
            <PersonalDetails :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-show="activeTab === 'family'">
            <FamilyBackground :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-show="activeTab === 'education'">
            <EducationBackground :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-show="activeTab === 'eligibility'">
            <CivilServiceEligibility :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-show="activeTab === 'work'">
            <WorkExperience :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-show="activeTab === 'voluntary'">
            <VoluntaryInvolvement :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-show="activeTab === 'training'">
            <TrainingAttended :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-show="activeTab === 'other'">
            <OtherRelevantInfo :internalForm="internalForm" :errors="props.errors" />
        </div>

        <div v-if="activeTab === 'employment'" class="form-grid">

            <div class="input-wrapper">
                <label>Department</label>
                <select v-model="internalForm.department_id">
                    <option value="">Select Department</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                        {{ dept.department_name }}
                    </option>
                </select>
            </div>

            <div class="input-wrapper">
                <label>Plantilla Item Number</label>
                <select v-model="internalForm.plantilla_id">
                    <option value="">Select Plantilla</option>
                    <option v-for="p in plantillas" :key="p.id" :value="p.id">
                        {{ p.plantilla_item_number }}
                    </option>
                </select>
            </div>

        </div>
    </form>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";
import Button from "@/components/Common/Button.vue";
// Only need what is actually used
import { reactive, watch, defineProps, defineEmits, defineExpose } from "vue";
const departments = ref([]);
const plantillas = ref([]);
const modules = import.meta.glob("@/components/PDS/FormSections/*.vue", { eager: true });

const components = {};
for (const path in modules) {
    const name = path.split("/").pop().replace(".vue", "");
    components[name] = modules[path].default;
}

const {
    PersonalDetails,
    FamilyBackground,
    EducationBackground,
    CivilServiceEligibility,
    WorkExperience,
    VoluntaryInvolvement,
    TrainingAttended,
    OtherRelevantInfo
} = components;

const props = defineProps({
    activeTab: {
        type: String,
        required: true
    },
    formData: {
        type: Object,
        default: () => ({})
    },
    // The errors object is received here from the parent/vee-validate composable
    errors: Object,
    validateField: Function,
    // The parent is no longer passing 'validate' to this component, as it calls it directly.
    // validate: Function // REMOVED: Parent handles validation directly
});

// Removed 'update:activeTab' emit since the parent controls navigation
const emit = defineEmits(['update:formData']);

// Ensure you are using 'props.formData' for correct initialization
const internalForm = reactive({
    // Personal
    last_name: (typeof props.formData !== 'undefined' && props.formData.last_name) ? props.formData.last_name : "",
    first_name: (typeof props.formData !== 'undefined' && props.formData.first_name) ? props.formData.first_name : "",
    middle_name: (typeof props.formData !== 'undefined' && props.formData.middle_name) ? props.formData.middle_name : "",
    name_extension: (typeof props.formData !== 'undefined' && props.formData.name_extension) ? props.formData.name_extension : "",
    birth_date: (typeof props.formData !== 'undefined' && props.formData.birth_date) ? props.formData.birth_date : "",
    place_of_birth: (typeof props.formData !== 'undefined' && props.formData.place_of_birth) ? props.formData.place_of_birth : "",
    sex: (typeof props.formData !== 'undefined' && props.formData.sex) ? props.formData.sex : "",
    civil_status: (typeof props.formData !== 'undefined' && props.formData.civil_status) ? props.formData.civil_status : "",
    height: (typeof props.formData !== 'undefined' && props.formData.height) ? props.formData.height : "",
    weight: (typeof props.formData !== 'undefined' && props.formData.weight) ? props.formData.weight : "",
    blood_type: (typeof props.formData !== 'undefined' && props.formData.blood_type) ? props.formData.blood_type : "",
    gsis_id_no: (typeof props.formData !== 'undefined' && props.formData.gsis_id_no) ? props.formData.gsis_id_no : "",
    pagibig_id_no: (typeof props.formData !== 'undefined' && props.formData.pagibig_id_no) ? props.formData.pagibig_id_no : "",
    philhealth_no: (typeof props.formData !== 'undefined' && props.formData.philhealth_no) ? props.formData.philhealth_no : "",
    sss_no: (typeof props.formData !== 'undefined' && props.formData.sss_no) ? props.formData.sss_no : "",
    tin_no: (typeof props.formData !== 'undefined' && props.formData.tin_no) ? props.formData.tin_no : "",
    agency_employee_no: (typeof props.formData !== 'undefined' && props.formData.agency_employee_no) ? props.formData.agency_employee_no : "",
    citizenship: (typeof props.formData !== 'undefined' && props.formData.citizenship) ? props.formData.citizenship : "",
    residential_address: (typeof props.formData !== 'undefined' && props.formData.residential_address) ? props.formData.residential_address : "",
    residential_zip: (typeof props.formData !== 'undefined' && props.formData.residential_zip) ? props.formData.residential_zip : "",
    permanent_address: (typeof props.formData !== 'undefined' && props.formData.permanent_address) ? props.formData.permanent_address : "",
    permanent_zip: (typeof props.formData !== 'undefined' && props.formData.permanent_zip) ? props.formData.permanent_zip : "",
    email: (typeof props.formData !== 'undefined' && props.formData.email) ? props.formData.email : "",
    telephone_no: (typeof props.formData !== 'undefined' && props.formData.telephone_no) ? props.formData.telephone_no : "",
    mobile_no: (typeof props.formData !== 'undefined' && props.formData.mobile_no) ? props.formData.mobile_no : "",
    ctc_place_of_issuance: (typeof props.formData !== 'undefined' && props.formData.ctc_place_of_issuance) ? props.formData.ctc_place_of_issuance : "",
    ctc_number: (typeof props.formData !== 'undefined' && props.formData.ctc_number) ? props.formData.ctc_number : "",
    ctc_date_of_issuance: (typeof props.formData !== 'undefined' && props.formData.ctc_date_of_issuance) ? props.formData.ctc_date_of_issuance : "",
    department_id: props.formData?.department_id ?? null,
    plantilla_id: props.formData?.plantilla_id ?? null,
    // Family
    spouse: (typeof props.formData !== 'undefined' && props.formData.spouse) ? props.formData.spouse : { name: "", birth_date: "", relationship: "", occupation: "", business_address: "", telephone_no: "", employer: "" },
    father: (typeof props.formData !== 'undefined' && props.formData.father) ? props.formData.father : { name: "", birth_date: "", relationship: "", occupation: "", business_address: "", telephone_no: "", employer: "" },
    mother: (typeof props.formData !== 'undefined' && props.formData.mother) ? props.formData.mother : { name: "", birth_date: "", relationship: "", occupation: "", business_address: "", telephone_no: "", employer: "" },
    children: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.children) && props.formData.children.length) ? props.formData.children : [{ full_name: "", birth_date: "" }],

    // Education
    educations: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.educations) && props.formData.educations.length) ? props.formData.educations : [{
        highest_educational_attainment: "",
        school_name: "",
        attendance_from: "",
        attendance_to: "",
        scholarships: "",
        year_graduated: "",
        highest_level_units: "",
        degree_course: ""
    }],

    // Eligibility
    eligibilities: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.eligibilities) && props.formData.eligibilities.length) ? props.formData.eligibilities : [{
        career_service: "",
        rating: "",
        date_of_examination: "",
        place_of_examination: "",
        license_no: "",
        date_of_validity: ""
    }],

    // Work
    work_experiences: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.work_experiences) && props.formData.work_experiences.length) ? props.formData.work_experiences : [{
        position_title: "",
        agency: "",
        monthly_salary: "",
        status_of_appointment: "",
        inclusive_from: "",
        inclusive_to: "",
        salary_job_grade: ""
    }],

    // Voluntary
    voluntary_works: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.voluntary_works) && props.formData.voluntary_works.length) ? props.formData.voluntary_works : [{
        organization_name: "",
        organization_address: "",
        from: "",
        to: "",
        position: "",
        hours: "",
        nature_of_work: ""
    }],

    // Trainings
    trainings: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.trainings) && props.formData.trainings.length) ? props.formData.trainings : [{
        title: "",
        from: "",
        to: "",
        type: "",
        conducted_by: ""
    }],

    // Other infos
    other_infos: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.other_infos) && props.formData.other_infos.length) ? props.formData.other_infos : [{
        special_skills: "",
        distinctions: "",
        membership: "",
        sponsored_by: ""
    }],

    // References
    references: (typeof props.formData !== 'undefined' && Array.isArray(props.formData.references) && props.formData.references.length) ? props.formData.references : [{ fullname: "", telephone_no: "", address: "" }]
});


onMounted(async () => {
    try {

        const deptResponse = await axios.get("/api/departments");
        departments.value = deptResponse.data;

        const plantillaResponse = await axios.get("api/plantillas");
        plantillas.value = plantillaResponse.data;

    } catch (error) {
        console.error("Failed to load employment data:", error);
    }
});

// The remaining functions (addChild, removeChild, addReference, removeReference)
// were moved to the end of the script for organizational cleanliness.

// -----------------------------------------------------------
// START: Simplified Exposure and Data Watch
// -----------------------------------------------------------

// Watch internal form changes and emit to parent (PDSFormModal)
watch(internalForm, (newVal) => {
    emit('update:formData', JSON.parse(JSON.stringify(newVal)));
}, { deep: true });

// Expose internal form data and utility methods only if needed by parent,
// but since the parent uses v-model:formData, this is mainly for debugging
// or complex parent/child communication (which is now done via props/emits).
// We remove the complex tab validity logic.
defineExpose({
    // Removed: tabValidity
    // Removed: handleTabChange
    internalForm // Keep exposed for potential direct access if needed
});

// -----------------------------------------------------------
// END: Simplified Exposure and Data Watch
// -----------------------------------------------------------

function addChild() {
    internalForm.children.push({ full_name: "", birth_date: "" });
}
function removeChild(index) {
    internalForm.children.splice(index, 1);
}

function addReference() {
    internalForm.references.push({ fullname: "", telephone_no: "", address: "" });
}
function removeReference(index) {
    internalForm.references.splice(index, 1);
}
</script>