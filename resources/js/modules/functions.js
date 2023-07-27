import i18n from './i18n';
import store from './store';
import _, { isEmpty } from 'lodash';

export default {
    install(Vue, options) {
        Vue.prototype.$functions = {
            ApiErrorsWatcher(apierrors,observer){
                if (observer) {
                    for (var prop in apierrors) {
                        let errorsJSON = []
                        if (apierrors.hasOwnProperty(prop)) {
                            let errorname = prop;
                            let err = apierrors[prop];
                            let errorvalue = err[0];
                            if (observer.fields) {
                                const field = observer.fields[errorname]
                                if (field) {
                                    errorsJSON[errorname] = [errorvalue]
                                }
                            }
                        }
                        observer.setErrors(errorsJSON)
                    }
                }
            },
            checkPassword(strPassword) {
                var m_strUpperCase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                var m_strLowerCase = "abcdefghijklmnopqrstuvwxyz";
                var m_strNumber = "0123456789";
                var m_strCharacters = "!@#$%^&*?_~"

                function countContain(countPassword, strCheck) {
                    // Declare variables
                    var nCount = 0;

                    for (let i = 0; i < countPassword.length; i++) {
                        if (strCheck.indexOf(countPassword.charAt(i)) > -1) {
                            nCount++;
                        }
                    }

                    return nCount;
                }
                // Reset combination count
                var nScore = 0;

                // Password length
                // -- Less than 4 characters
                if (strPassword.length < 5) {
                    nScore += 5;
                }
                // -- 5 to 7 characters
                else if (strPassword.length > 4 && strPassword.length < 8) {
                    nScore += 15;
                }
                // -- 8 or more
                else if (strPassword.length > 7) {
                    nScore += 30;
                }

                // Letters
                var nUpperCount = countContain(strPassword, m_strUpperCase);
                var nLowerCount = countContain(strPassword, m_strLowerCase);
                var nLowerUpperCount = nUpperCount + nLowerCount;
                // -- Letters are all lower case
                if (nUpperCount == 0 && nLowerCount != 0) {
                    nScore += 10;
                }
                // -- Letters are upper case and lower case
                else if (nUpperCount != 0 && nLowerCount != 0) {
                    nScore += 25;
                }

                // Numbers
                var nNumberCount = countContain(strPassword, m_strNumber);
                // -- 1 number
                if (nNumberCount == 1) {
                    nScore += 10;
                }
                // -- 2 number
                if (nNumberCount == 2) {
                    nScore += 15;
                }
                // -- 3 or more numbers
                if (nNumberCount >= 3) {
                    nScore += 20;
                }

                // Characters
                var nCharacterCount = countContain(strPassword, m_strCharacters);
                // -- 1 character
                if (nCharacterCount == 1) {
                    nScore += 10;
                }
                // -- More than 1 character
                if (nCharacterCount > 1) {
                    nScore += 25;
                }

                // Bonus
                // -- Letters and numbers
                if (nNumberCount != 0 && nLowerUpperCount != 0) {
                    nScore += 2;
                }
                // -- Letters, numbers, and characters
                if (nNumberCount != 0 && nLowerUpperCount != 0 && nCharacterCount != 0) {
                    nScore += 3;
                }
                // -- Mixed case letters, numbers, and characters
                if (nNumberCount != 0 && nUpperCount != 0 && nLowerCount != 0 && nCharacterCount != 0) {
                    nScore += 5;
                }
                if (nScore > 100) {
                    nScore = 100;
                }
                return nScore;
            },
            passwordScoreColor(displayedScore = 0) {
                let defaultcolor = 'error'
                if (displayedScore > 2) {
                    defaultcolor = 'error'
                }
                if (displayedScore > 4) {
                    defaultcolor = 'warning'
                }
                if (displayedScore > 6) {
                    defaultcolor = 'success'
                }
                if (displayedScore > 8) {
                    defaultcolor = 'success'
                }
                return defaultcolor
            },
            passwordScoreMessage(displayedScore = 0) {
                let defaultmessage = i18n.t('auth.passwordmesage.veryweak')
                if (displayedScore > 2) {
                    defaultmessage = i18n.t('auth.passwordmesage.weak')
                }
                if (displayedScore > 4) {
                    defaultmessage = i18n.t('auth.passwordmesage.medium')
                }
                if (displayedScore > 6) {
                    defaultmessage = i18n.t('auth.passwordmesage.good')
                }
                if (displayedScore > 8) {
                    defaultmessage = i18n.t('auth.passwordmesage.great')
                }
                return defaultmessage
            },
            ObjectAreEqual(obj1, obj2) {
                var $string1 = JSON.stringify(_.omit(obj1, _.functions(obj1)))
                var $string2 = JSON.stringify(_.omit(obj2, _.functions(obj2)))
                if (typeof String.prototype.replaceAll == "undefined") {
                    String.prototype.replaceAll = function (match, replace) {
                        return this.replace(new RegExp(match, 'g'), () => replace);
                    }
                }
                $string1 = $string1.replaceAll("true", "1");
                $string1 = $string1.replaceAll("false", "0");
                $string2 = $string2.replaceAll("true", "1");
                $string2 = $string2.replaceAll("false", "0");
                return _.isEqual($string1, $string2)
            },
            scrollToError(self) {
                self.$nextTick(() => {
                    var el = self.$el.getElementsByClassName("v-messages__message")[0];
                    if (!el) {
                        el = self.$parent.$el.getElementsByClassName("v-messages__message")[0];
                    }
                    if (!el) {
                        el = document.querySelector(".v-messages.error--text:first-of-type");
                    }
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
                    }
                })
            },
            isInDateRange(value, range) {
                if (range.indexOf(value) !== -1) {
                    return true
                }
                return false
            },
            isAfterToday(val) {
                if (moment(val, 'YYYY-MM-DD').isAfter()) {
                    return true
                }
                return false
            },
            isBeforeToday(val) {
                if (moment(val, 'YYYY-MM-DD').isBefore()) {
                    return true
                }
                return false
            },
            actionOnSelection(response, selection_array) {
                if (response.object) {
                    if (response.object.id) {
                        let finded = selection_array.findIndex(x => {
                            if (x.trashable_id) {
                                return x.trashable_id === response.object.id
                            }
                            else {
                                return x.id === response.object.id
                            }
                        });
                        if (finded != -1) {
                            selection_array.splice(finded, 1)
                        }
                    }
                }
                store.dispatch('handleResponseMessage', response)
            },
            isEmpty(object) {
                return _.isEmpty(object)
            },
            renderErrorMessage(errors = [], vid = '', name = '') {
                if (errors[0]) {
                    return errors[0].replace(vid, name)
                }
                return null
            },
            openLink(url = null, type = 'link', filename = 'download') {
                if (url != null) {
                    let link = document.createElement('a')
                    switch (type) {
                        case 'link':
                            link.href = url
                            break;
                        case 'email':
                            link.href = "mailto:" + url
                            break;
                        case 'phone':
                            link.href = "tel:" + url
                            break;
                        case 'map':
                            link.href = "https://maps.google.com/?q=" + url
                            break;
                        case 'download':
                            link.href = url
                            link.download = filename
                            break;
                        default:
                            break;
                    }
                    link.target = '_blank'
                    link.click()
                    setTimeout(function () {
                        window.URL.revokeObjectURL(link.href)
                    }, 100)
                }
            },
        }
    }
}