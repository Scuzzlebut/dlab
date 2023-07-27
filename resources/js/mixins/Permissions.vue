<script>
export default {
    methods: {
        $can(permissionCode) {
            return this.$appOptions.permissions(permissionCode).indexOf(this.$store.getters.getMe?.role_name ?? null)!=-1
        },
        $hasRole(roleName) {
            return this.$store.getters.getMe?.role_name == roleName;
        },
    },
};
</script>
