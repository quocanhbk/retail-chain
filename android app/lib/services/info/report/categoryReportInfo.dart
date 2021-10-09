class CategoryReportInfo{
  List<Category> totalSellPriceCategory;
  int totalSellPrice = 0;
  CategoryReportInfo({required this.totalSellPriceCategory}){
    this.totalSellPriceCategory.sort((a,b){
      return b.totalSellPrice.compareTo(a.totalSellPrice);
    });
    this.totalSellPriceCategory.forEach((element) {
      totalSellPrice+=element.totalSellPrice;
    });
  }
}
class Category{
  late int id;
  late String name;
  late int totalSellPrice;
  late int totalQuantity;

  Category({required String id, required String name, required String totalSellPrice, required String totalQuantity}){
    this.id = int.tryParse(id)??0;
    this.name = name;
    this.totalSellPrice = int.tryParse(totalSellPrice)??0;
    this.totalQuantity=int.tryParse(totalQuantity)??0;
  }
}