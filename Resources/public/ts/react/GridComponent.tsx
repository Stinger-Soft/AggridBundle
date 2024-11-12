const enterprise = process?.env?.AGGRID_ENABLE_ENTERPRISE || false;
export {GridComponent} from "./GridComponentCommunity";

if(enterprise) {
    import("./GridComponentEnterprise");
}
