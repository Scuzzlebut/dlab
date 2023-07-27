import "./bootstrap";
import "@fortawesome/fontawesome-free/scss/fontawesome.scss";
import "@fortawesome/fontawesome-free/scss/brands.scss";
import "@fortawesome/fontawesome-free/scss/regular.scss";
import "@fortawesome/fontawesome-free/scss/solid.scss";
import Vue from "vue";
import moment from "moment";
import "moment/dist/locale/it";
import "moment-duration-format";
import formatters from "./modules/formatters";
import functions from "./modules/functions";
import i18n from "./modules/i18n";
import options from "./modules/options";
import { router } from "./modules/routes";
import store from "./modules/store";
import vuetify from "./modules/vuetify";
import Permissions from "./mixins/Permissions.vue";

window.moment = moment;
Vue.use(formatters);
Vue.use(functions);
Vue.use(options);
Vue.mixin(Permissions);

//VALIDATOR
import { localize } from "vee-validate";
import { ValidationObserver } from "vee-validate";
import { ValidationProvider } from "vee-validate";

import en from "vee-validate/dist/locale/en.json";
import it from "vee-validate/dist/locale/it.json";

Vue.component("ValidationProvider", ValidationProvider);
Vue.component("ValidationObserver", ValidationObserver);

localize({
    en,
    it,
});
localize("it");
import { extend } from "vee-validate";
import * as rules from "vee-validate/dist/rules";
import { setInteractionMode } from "vee-validate";

setInteractionMode("eager");
Object.keys(rules).forEach((rule) => {
    extend(rule, {
        ...rules[rule],
    });
});

extend("date_between", {
    params: ["firstDate", "secondDate"],
    validate: (value, { firstDate, secondDate }) => {
        if (moment(value, "DD/MM/YYYY").isSameOrAfter(moment(firstDate, "DD/MM/YYYY")) && moment(value, "DD/MM/YYYY").isSameOrBefore(moment(secondDate, "DD/MM/YYYY"))) {
            return true;
        }
        return false;
    },
    message: "La data inserita deve essere compresa tra {firstDate} e {secondDate}.",
});
extend("date_format", {
    validate: (value) => {
        if (moment(value, "DD/MM/YYYY", true).isValid()) {
            return true;
        }
        return false;
    },
    message: "La data inserita deve essere nel formato GG/MM/AAAA",
});

/************************************** COMPONENTS  ********************************/

//COMMONS
import AttachmentsBadge from "./components/_commons/AttachmentsBadge.vue";
import Attachments from "./components/_commons/Attachments.vue";
import BaseListItem from "./components/_commons/BaseListItem.vue";
import BaseListItemGroup from "./components/_commons/BaseListItemGroup.vue";
import ContentValue from "./components/_commons/ContentValue.vue";
import CreateEditLayout from "./components/_commons/CreateEditLayout.vue";
import DateField from "./components/_commons/DateField.vue";
import ImageZoom from "./components/_commons/ImageZoom.vue";
import InstantEdit from "./components/_commons/InstantEdit.vue";
import MainContainer from "./components/_commons/MainContainer.vue";
import MaterialButton from "./components/_commons/MaterialButton.vue";
import MaterialCard from "./components/_commons/MaterialCard.vue";
import MaterialDialog from "./components/_commons/MaterialDialog.vue";
import MaterialExport from "./components/_commons/MaterialExport.vue";
import MaterialPopup from "./components/_commons/MaterialPopup.vue";
import MaterialSnackbar from "./components/_commons/MaterialSnackbar.vue";
import MaterialTable from "./components/_commons/MaterialTable.vue";
import SearchBox from "./components/_commons/SearchBox.vue";
import SlotNoData from "./components/_commons/SlotNoData.vue";
import TimeField from "./components/_commons/TimeField.vue";
import UploadFile from "./components/_commons/UploadFile.vue";
Vue.component("attachments-badge", AttachmentsBadge);
Vue.component("attachments", Attachments);
Vue.component("base-listitem", BaseListItem);
Vue.component("base-listitemgroup", BaseListItemGroup);
Vue.component("content-value", ContentValue);
Vue.component("create-edit-layout", CreateEditLayout);
Vue.component("date-field", DateField);
Vue.component("image-zoom", ImageZoom);
Vue.component("instant-edit", InstantEdit);
Vue.component("main-container", MainContainer);
Vue.component("material-button", MaterialButton);
Vue.component("material-card", MaterialCard);
Vue.component("material-dialog", MaterialDialog);
Vue.component("material-export", MaterialExport);
Vue.component("material-popup", MaterialPopup);
Vue.component("material-snackbar", MaterialSnackbar);
Vue.component("material-table", MaterialTable);
Vue.component("search-box", SearchBox);
Vue.component("slot-no-data", SlotNoData);
Vue.component("time-field", TimeField);
Vue.component("upload-file", UploadFile);

//ATTENDANCE
import CreateEditAttendance from "./components/attendance/CreateEditAttendance.vue";
import CalendarElement from "./components/attendance/CalendarElement.vue";
import CalendarFocus from "./components/attendance/CalendarFocus.vue";
import CalendarSelectDay from "./components/attendance/CalendarSelectDay.vue";
Vue.component("create-edit-attendance", CreateEditAttendance);
Vue.component("calendar-element", CalendarElement);
Vue.component("calendar-focus", CalendarFocus);
Vue.component("calendar-select-day", CalendarSelectDay);

//AUTH
import AuthLayout from "./components/auth/AuthLayout.vue";
Vue.component("auth-layout", AuthLayout);

//COMMUNICATION
import CommunicationToRead from "./components/communication/CommunicationToRead.vue";
import CreateEditCommunication from "./components/communication/CreateEditCommunication.vue";
Vue.component("communication-toread", CommunicationToRead);
Vue.component("create-edit-communication", CreateEditCommunication);

//HTML-EDITOR
import HtmlEditor from "./components/htmleditor/HtmlEditor.vue";
Vue.component("html-editor", HtmlEditor);

//PAYSHEET
import CreateEditPaysheet from "./components/paysheet/CreateEditPaysheet.vue";
import ImportPaysheet from "./components/paysheet/ImportPaysheet.vue";
Vue.component("create-edit-paysheet", CreateEditPaysheet);
Vue.component("import-paysheet", ImportPaysheet);

//STAFF
import CreateEditStaff from "./components/staff/CreateEditStaff.vue";
import RelatedStaff from "./components/staff/RelatedStaff.vue";
Vue.component("create-edit-staff", CreateEditStaff);
Vue.component("related-staff", RelatedStaff);

const app = new Vue({
    el: "#app",
    beforeCreate() {
        this.$store.commit("getAccessFromStorage");
    },
    created() {
        window.axios.interceptors.response.use(
            function (response) {
                return response;
            },
            function (error) {
                switch (error.response.status) {
                    case 401:
                        if (store.getters.isAuthenticated) {
                            store.commit("authLogout");
                            router.push("/login");
                            setTimeout(() => {
                                store.commit("showSnackbar", {
                                    message: i18n.t("global.session_expired"),
                                    color: "error",
                                    duration: 3000,
                                });
                            }, 500);
                        } else {
                            if (router.history.current.path !== "/login") {
                                router.push("/login");
                            }
                        }
                        return Promise.reject({ ...error });
                        break;
                    default:
                        return Promise.reject({ ...error });
                        break;
                }
            }
        );
    },
    vuetify,
    router,
    store,
    i18n,
});
