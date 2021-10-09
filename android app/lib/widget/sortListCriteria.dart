import 'package:bkrm/widget/listProducts.dart';
import 'package:bkrm/widget/productItem.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

import 'enumDefine.dart';

class SortListCriteriaProduct extends StatefulWidget {
  final ListProduct listProduct;
  SortCriteria? sortCriteria=SortCriteria.dateCreateDescending;
  Criteria? selectedCriteria = Criteria.date;
  SortListCriteriaProduct(this.listProduct);

  @override
  _SortListCriteriaProductState createState() => _SortListCriteriaProductState();
}

class _SortListCriteriaProductState extends State<SortListCriteriaProduct> {
  Widget radioButtonGroup = Container();

  @override
  void initState() {
    super.initState();
    buildRadioButton();
  }

  void buildRadioButton() {
    switch (widget.selectedCriteria) {
      case Criteria.name:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo thứ tự thấp đến cao "),
                Radio(value: SortCriteria.nameAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listProduct.sortProduct(orderBy: "item_name",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(value: SortCriteria.nameDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listProduct.sortProduct(orderBy: "item_name",order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
      case Criteria.price:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo thứ tự thấp đến cao "),
                Radio(value: SortCriteria.priceAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listProduct.sortProduct(orderBy: "sell_price",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(value: SortCriteria.priceDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listProduct.sortProduct(orderBy: "sell_price",order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
      case Criteria.date:
        radioButtonGroup = Column(
          children: [
            Row(
              children: [
                Text("Theo thứ tự thấp đến cao "),
                Radio(value: SortCriteria.dateCreateAscending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listProduct.sortProduct(orderBy: "created_date",order: "asc");
                      Navigator.pop(context);
                    })
              ],
            ),
            Row(
              children: [
                Text("Theo thứ tự cao đến thấp "),
                Radio(value: SortCriteria.dateCreateDescending,
                    groupValue: widget.sortCriteria,
                    onChanged: (dynamic value) {
                      widget.sortCriteria = value;
                      widget.listProduct.sortProduct(orderBy: "created_date",order: "desc");
                      Navigator.pop(context);
                    })
              ],
            )
          ],
        );
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Sắp xếp"),
      content:
      Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
              children: [DropdownButton(
                  value: widget.selectedCriteria,
                  items: <DropdownMenuItem>[
                    DropdownMenuItem(
                      child: Text("Tên sản phẩm"), value: Criteria.name,),
                    DropdownMenuItem(
                      child: Text("Giá bán"), value: Criteria.price,),
                    DropdownMenuItem(
                      child: Text("Ngày thêm sản phẩm"), value: Criteria.date,),
                  ],
                  onChanged: (dynamic value) {
                    setState(() {
                      widget.selectedCriteria = value;
                      buildRadioButton();
                    });
                  }),
              ]
          ),
          radioButtonGroup
        ],
      ),

    );
  }
}

