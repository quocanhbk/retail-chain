class CustomerReportInfo{
  List<Customer> listCustomer;

  CustomerReportInfo(this.listCustomer);
}
class Customer{
  String name;
  late int totalBuyPrice;

  Customer({required this.name, required String totalBuyPrice}){
    this.totalBuyPrice=int.tryParse(totalBuyPrice)??0;
  }
}