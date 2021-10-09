class SupplierReportInfo{
  List<Supplier> listSupplier;

  SupplierReportInfo(this.listSupplier);
}
class Supplier{
  String name;
  late int totalPurchasePrice;

  Supplier({required this.name, required String totalPurchasePrice}){
    this.totalPurchasePrice=int.tryParse(totalPurchasePrice)??0;
  }
}