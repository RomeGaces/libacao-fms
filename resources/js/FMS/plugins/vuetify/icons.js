import { h } from 'vue'

// ✅ Import custom 3D Fluent icon (PNG)
import fountainPen from '@/../icons/fountain_pen_3d.png'
import officeBuilding from '@/../icons/office_building_3d.png'

// ✅ Import checkboxes & radios (SVGs)
import checkboxChecked from '@images/svg/checkbox-checked.svg'
import checkboxIndeterminate from '@images/svg/checkbox-indeterminate.svg'
import checkboxUnchecked from '@images/svg/checkbox-unchecked.svg'
import radioChecked from '@images/svg/radio-checked.svg'
import radioUnchecked from '@images/svg/radio-unchecked.svg'

// ✅ Map custom icons
const customIcons = {
  'mdi-checkbox-blank-outline': checkboxUnchecked,
  'mdi-checkbox-marked': checkboxChecked,
  'mdi-minus-box': checkboxIndeterminate,
  'mdi-radiobox-marked': radioChecked,
  'mdi-radiobox-blank': radioUnchecked,

  // 🔹 Your Windows 11 style 3D icon
  officeBuilding,
  fountainPen,
}

const aliases = {
  calendar: 'bx-calendar',
  collapse: 'bx-chevron-up',
  complete: 'bx-check',
  cancel: 'bx-x',
  close: 'bx-x',
  delete: 'bx-bxs-x-circle',
  clear: 'bx-x-circle',
  success: 'bx-check-circle',
  info: 'bx-info-circle',
  warning: 'bx-error',
  error: 'bx-error-circle',
  prev: 'bx-chevron-left',
  ratingEmpty: 'bx-star',
  ratingFull: 'bx-bxs-star',
  ratingHalf: 'bx-bxs-star-half',
  next: 'bx-chevron-right',
  delimiter: 'bx-circle',
  sort: 'bx-up-arrow-alt',
  expand: 'bx-chevron-down',
  menu: 'bx-menu',
  subgroup: 'bx-caret-down',
  dropdown: 'bx-chevron-down',
  edit: 'bx-pencil',
  loading: 'bx-refresh',
  first: 'bx-skip-previous',
  last: 'bx-skip-next',
  unfold: 'bx-move-vertical',
  file: 'bx-paperclip',
  plus: 'bx-plus',
  minus: 'bx-minus',
  sortAsc: 'bx-up-arrow-alt',
  sortDesc: 'bx-down-arrow-alt',
}

export const iconify = {
  component: props => {
    if (typeof props.icon === 'string') {
      const iconComponent = customIcons[props.icon]

      // ✅ Handle imported PNG/SVG assets
      if (iconComponent) {
        return h('img', {
          src: iconComponent,
          style: {
            width: '24px',
            height: '24px',
            objectFit: 'contain',
          },
        })
      }
    }

    // ✅ Fallback to class-based icons
    return h(props.tag, {
      ...props,
      class: [props.icon],
      tag: undefined,
      icon: undefined,
    })
  },
}

export const icons = {
  defaultSet: 'iconify',
  aliases,
  sets: {
    iconify,
  },
}
