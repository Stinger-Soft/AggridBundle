declare const process: { env: Record<string, string | undefined> };
const enterprise = process?.env.AGGRID_ENABLE_ENTERPRISE;
export {GridComponent} from "./GridComponentCommunity";

if (enterprise) {
    void import("./GridComponentEnterprise");
}
