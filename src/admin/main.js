import VendorPro from 'admin/components/VendorPro.vue'
import VendorSingle from 'admin/components/VendorSingle.vue'
import StoreCategoriesIndex from 'admin/components/StoreCategoriesIndex.vue'
import StoreCategoriesShow from 'admin/components/StoreCategoriesShow.vue'
import Modules from 'admin/components/Modules.vue'
import Announcement from 'admin/components/Announcement.vue'
import NewAnnouncement from 'admin/components/NewAnnouncement.vue'
import EditAnnouncement from 'admin/components/EditAnnouncement.vue'
import Refund from 'admin/components/Refund.vue'
import Tools from 'admin/components/Tools.vue'
import Reports from 'admin/components/Reports.vue'

dokan_add_route(VendorSingle)
dokan_add_route(StoreCategoriesIndex)
dokan_add_route(StoreCategoriesShow)
dokan_add_route(Modules)
dokan_add_route(Announcement)
dokan_add_route(NewAnnouncement)
dokan_add_route(EditAnnouncement)
dokan_add_route(Refund)
dokan_add_route(Tools)
dokan_add_route(Reports)

dokan.addFilterComponent('getDokanVendorHeaderArea', 'dokanVendor', VendorPro );