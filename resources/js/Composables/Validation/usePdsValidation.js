import { ref, computed } from "vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

export function usePdsValidation(activeTab, initialFormData) {
    const schemas = {
        personal: yup.object({
            first_name: yup.string().required("First name is required."),
            last_name: yup.string().required("Last name is required."),
            middle_name: yup.string().required("Middle name is required"),
            name_extension: yup.string().nullable(),
            birth_date: yup
                .date()
                .required("Date of birth is required.")
                .transform((value, original) => (original === "" ? null : value)),
            place_of_birth: yup.string().required("Place of birth required."),
            sex: yup.string().required("Sex is required."),
            civil_status: yup.string().required("Civil status is required."),
            citizenship: yup.string().required("Citizenship is required."),
            height: yup.string().required("Height is required."),
            weight: yup.string().required("Weight is required."),
            blood_type: yup.string().required("Blood type is required."),
            gsis_id_no: yup.string().nullable(),
            pagibig_id_no: yup.string().nullable(),
            philhealth_no: yup.string().nullable(),
            sss_no: yup.string().nullable(),
            tin_no: yup.string().nullable(),
            agency_employee_no: yup.string().nullable(),
            residential_address: yup.string().required("Residential address is required."),
            residential_zip: yup.string().required("Residential zip is required."),
            permanent_address: yup.string().required("Permanent address is required."),
            permanent_zip: yup.string().required("Permanent zip is required."),
            telephone_no: yup.string().required("Telephone no. is required."),
            mobile_no: yup.string().required("Mobile no. is required."),
            email: yup.string().required("Email is required.").email("Invalid email format"),
            ctc_number: yup.string().required("CTC no. is required."),
            ctc_place_of_issuance: yup.string().required("CTC place of issuance is required."),
            ctc_date_of_issuance: yup
                .date()
                .required("CTC issuance date is required.")
                .transform((value, original) => (original === "" ? null : value)),
            plantilla_id: yup.number().nullable(),
            department_id: yup.number().nullable(),
        }),
        family: yup.object({
            // Spouse
            spouse: yup.object({
                name: yup.string().nullable(),
                birth_date: yup
                    .date()
                    .nullable()
                    .transform((value, original) => (original === "" ? null : value)),
                occupation: yup.string().nullable(),
                employer: yup.string().nullable(),
                business_address: yup.string().nullable(),
                telephone_no: yup.string().nullable(),
                relationship: yup.string().nullable(),
            }),

            // Father
            father: yup.object({
                name: yup.string().nullable(),
                birth_date: yup
                    .date()
                    .nullable()
                    .transform((value, original) => (original === "" ? null : value)),
                occupation: yup.string().nullable(),
                employer: yup.string().nullable(),
                business_address: yup.string().nullable(),
                telephone_no: yup.string().nullable(),
                relationship: yup.string().nullable(),
            }),

            // Mother
            mother: yup.object({
                name: yup.string().nullable(),
                birth_date: yup
                    .date()
                    .nullable()
                    .transform((value, original) => (original === "" ? null : value)),
                occupation: yup.string().nullable(),
                employer: yup.string().nullable(),
                business_address: yup.string().nullable(),
                telephone_no: yup.string().nullable(),
                relationship: yup.string().nullable(),
            }),

            // Children
            children: yup.array().of(
                yup.object({
                    full_name: yup.string().nullable(),
                    birth_date: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                })
            ),
        }),
        education: yup.object({
            educations: yup.array().of(
                yup.object({
                    highest_educational_attainment: yup.string().nullable(),
                    school_name: yup.string().nullable(),
                    degree_course: yup.string().nullable(),
                    year_graduated: yup.string().nullable(),
                    highest_level_units: yup
                        .number()
                        .transform((value, originalValue) => {
                            if (originalValue === "") return null;
                            return isNaN(value) ? null : value;
                        })
                        // .typeError("Highest level units must be a number")
                        .nullable(),
                    attendance_from: yup
                        .date()
                        .transform((value, original) => {
                            return original ? new Date(original) : null;
                        })
                        .nullable(),
                    attendance_to: yup
                        .date()
                        .transform((value, original) => {
                            return original ? new Date(original) : null;
                        })
                        .nullable(),
                    scholarships: yup.string().nullable(),
                })
            ),
        }),
        eligibility: yup.object({
            eligibilities: yup.array().of(
                yup.object({
                    career_service: yup.string().nullable(),
                    rating: yup
                        .number()
                        .transform((value, originalValue) => (originalValue === "" ? null : value))
                        // .typeError("Rating must be a number")
                        .nullable(),
                    exam_date: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                    place_of_examination: yup.string().nullable(),
                    license_no: yup.string().nullable(),
                    date_of_validity: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                })
            ),
        }),
        work: yup.object({
            work_experiences: yup.array().of(
                yup.object({
                    inclusive_from: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                    inclusive_to: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                    position_title: yup.string().nullable(),
                    agency: yup.string().nullable(),
                    monthly_salary: yup
                        .number()
                        .transform((value, originalValue) => (originalValue === "" ? null : value))
                        // .typeError("Monthly salary must be a number")
                        .nullable(),
                    salary_job_grade: yup.string().nullable(),
                    status_of_appointment: yup.string().nullable(),
                    is_gov_service: yup.boolean().nullable(),
                })
            ),
        }),

        voluntary: yup.object({
            voluntary_works: yup.array().of(
                yup.object({
                    organization_name: yup.string().nullable(),
                    organization_address: yup.string().nullable(),
                    from: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                    to: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                    hours: yup
                        .number()
                        .transform((value, originalValue) => (originalValue === "" ? null : value))
                        // .typeError("This must be a number")
                        .nullable(),
                    position: yup.string().nullable(),
                    nature_of_work: yup.string().nullable(),
                })
            ),
        }),
        training: yup.object({
            trainings: yup.array().of(
                yup.object({
                    title: yup.string().nullable(),
                    from: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                    to: yup
                        .date()
                        .nullable()
                        .transform((value, original) => (original === "" ? null : value)),
                    hours: yup
                        .number()
                        .transform((value, originalValue) => (originalValue === "" ? null : value))
                        // .typeError("This must be a number")
                        .nullable(),
                    conducted_by: yup.string().nullable(),
                })
            ),
        }),
        other: yup.object({
            other_infos: yup.array().of(
                yup.object({
                    special_skills: yup.string().nullable(),
                    distinctions: yup.string().nullable(),
                    membership: yup.string().nullable(),
                    sponsored_by: yup.string().nullable(),
                })
            ),
            references: yup.array().of(
                yup.object({
                    name: yup.string().nullable(),
                    telephone_no: yup.string().nullable(),
                    address: yup.string().nullable(),
                })
            ),
        }),
    };

    const formData = ref({
        ...initialFormData,

        // PERSONAL
        first_name: initialFormData.first_name ?? "",
        last_name: initialFormData.last_name ?? "",
        middle_name: initialFormData.middle_name ?? "",
        name_extension: initialFormData.name_extension ?? "",
        birth_date: initialFormData.birth_date ?? "",
        place_of_birth: initialFormData.place_of_birth ?? "",
        sex: initialFormData.sex ?? "",
        civil_status: initialFormData.civil_status ?? "",
        citizenship: initialFormData.citizenship ?? "",
        height: initialFormData.height ?? "",
        weight: initialFormData.weight ?? "",
        blood_type: initialFormData.blood_type ?? "",
        gsis_id_no: initialFormData.gsis_id_no ?? "",
        pagibig_id_no: initialFormData.pagibig_id_no ?? "",
        philhealth_no: initialFormData.philhealth_no ?? "",
        sss_no: initialFormData.sss_no ?? "",
        tin_no: initialFormData.tin_no ?? "",
        agency_employee_no: initialFormData.agency_employee_no ?? "",
        residential_address: initialFormData.residential_address ?? "",
        residential_zip: initialFormData.residential_zip ?? "",
        permanent_address: initialFormData.permanent_address ?? "",
        permanent_zip: initialFormData.permanent_zip ?? "",
        telephone_no: initialFormData.telephone_no ?? "",
        mobile_no: initialFormData.mobile_no ?? "",
        email: initialFormData.email ?? "",
        ctc_number: initialFormData.ctc_number ?? "",
        ctc_place_of_issuance: initialFormData.ctc_place_of_issuance ?? "",
        ctc_date_of_issuance: initialFormData.ctc_date_of_issuance ?? "",
        plantilla_id: initialFormData.plantilla_id ?? null,
        department_id: initialFormData.department_id ?? null,

        // FAMILY (top-level keys as you used in buildPayload)
        spouse: initialFormData.spouse ?? {
            name: "",
            birth_date: "",
            occupation: "",
            employer: "",
            business_address: "",
            telephone_no: "",
            relationship: "",
        },
        father: initialFormData.father ?? {
            name: "",
            birth_date: "",
            occupation: "",
            employer: "",
            business_address: "",
            telephone_no: "",
            relationship: "",
        },
        mother: initialFormData.mother ?? {
            name: "",
            birth_date: "",
            occupation: "",
            employer: "",
            business_address: "",
            telephone_no: "",
            relationship: "",
        },
        children: initialFormData.children ?? [
            {
                full_name: "",
                birth_date: "",
            },
        ],

        // EDUCATION (array)
        educations: initialFormData.educations ?? [
            {
                highest_educational_attainment: "",
                school_name: "",
                degree_course: "",
                year_graduated: "",
                highest_level_units: "",
                attendance_from: "",
                attendance_to: "",
                scholarships: "",
            },
        ],

        // ELIGIBILITY
        eligibilities: initialFormData.eligibilities ?? [
            {
                career_service: "",
                rating: "",
                exam_date: "",
                place_of_examination: "",
                license_no: "",
                date_of_validity: "",
            },
        ],

        // WORK EXPERIENCE
        work_experiences: initialFormData.work_experiences ?? [
            {
                inclusive_from: "",
                inclusive_to: "",
                position_title: "",
                agency: "",
                monthly_salary: "",
                salary_job_grade: "",
                status_of_appointment: "",
                is_gov_service: null,
            },
        ],

        // VOLUNTARY
        voluntary_works: initialFormData.voluntary_works ?? [
            {
                organization_name: "",
                organization_address: "",
                from: "",
                to: "",
                hours: "",
                position: "",
                nature_of_work: "",
            },
        ],

        // TRAININGS
        trainings: initialFormData.trainings ?? [
            {
                title: "",
                from: "",
                to: "",
                hours: "",
                conducted_by: "",
            },
        ],

        // OTHER
        other_infos: initialFormData.other_infos ?? [
            {
                special_skills: "",
                distinctions: "",
                membership: "",
                sponsored_by: "",
            },
        ],

        references: initialFormData.references ?? [
            {
                name: "",
                telephone_no: "",
                address: "",
            },
        ],
    });

    const { handleSubmit, errors, validate } = useForm({
        validationSchema: computed(() => schemas[activeTab.value]),
        initialValues: formData.value,
        // 💡 CRITICAL FIX: Set validateOnMount to false to prevent showing errors on initial load
        validateOnMount: false, 
        // Optional but recommended: Validate on blur or change to provide immediate feedback after user interaction
        validationBehavior: 'lazy', // or 'onBlur'
    });

    // Check all tabs if valid
    async function validateAllTabs(formData) {
        const invalidTabs = [];

        for (const key of Object.keys(schemas)) {
            try {
                // Ensure validation uses the latest data
                await schemas[key].validate(formData, { abortEarly: false });
            } catch (err) {
                invalidTabs.push(key);
            }
        }

        return {
            valid: invalidTabs.length === 0,
            invalidTabs,
        };
    }

    return {
        formData,
        handleSubmit,
        errors,
        validate,
        schemas,
        validateAllTabs,
    };
}