import 'dart:io';
import 'dart:typed_data';

import 'package:bkrm/services/info/sellingInfo/customerInfo.dart';
import 'package:bkrm/services/printer/qrCodePrinter.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:image_gallery_saver/image_gallery_saver.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:path_provider/path_provider.dart';
import 'package:intl/intl.dart';
class CustomerDetailPage extends StatefulWidget {
  CustomerInfo customerData;

  CustomerDetailPage(this.customerData);

  @override
  _CustomerDetailPageState createState() => _CustomerDetailPageState();
}

class _CustomerDetailPageState extends State<CustomerDetailPage> {
  final _formKey = GlobalKey<FormState>();
  final _scaffoldKey = GlobalKey<ScaffoldState>();

  bool editEnable = false;

  TextEditingController phoneNumberController = TextEditingController();
  TextEditingController nameController = TextEditingController();
  TextEditingController addressController = TextEditingController();
  TextEditingController emailController = TextEditingController();
  TextEditingController dateOfBirthController = TextEditingController();

  String? genderValue;
  DateTime? dateOfBirth;

  bool phoneNumberValid = true;
  bool nameValid = true;
  bool addressValid = true;
  bool emailValid = true;
  bool genderValid = true;

  bool needRefresh = false;

  @override
  void initState() {
    super.initState();
setUpCustomerInfo();
  }

  void setUpCustomerInfo(){
    phoneNumberController.text = widget.customerData.phoneNumber!;
    nameController.text =
    widget.customerData.name == null ? "" : widget.customerData.name!;
    addressController.text = widget.customerData.address == null
        ? ""
        : widget.customerData.address!;
    emailController.text =
    widget.customerData.email == null ? "" : widget.customerData.email!;
    genderValue = widget.customerData.gender;
    if(widget.customerData.dateOfBirth!=null){
      dateOfBirth=widget.customerData.dateOfBirth;
      dateOfBirthController.text=DateFormat("dd-MM-yyyy").format(widget.customerData.dateOfBirth!);
    }
  }

  clearPage(){
    phoneNumberController.clear();
    nameController.clear();
    addressController.clear();
    emailController.clear();
    dateOfBirthController.clear();
    genderValue=null;
    dateOfBirth=null;
  }

  void remoteSetState() {
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: Text("Th??ng tin kh??ch h??ng"),
      ),
      body: WillPopScope(
        onWillPop: ()async{
          Navigator.pop(context,needRefresh);
          return needRefresh;
      },
        child: SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 30,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 2,
                          child: Text(
                            "S??? ??i???n tho???i: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 5,
                        child: TextFormField(
                          enabled: editEnable,
                          keyboardType: TextInputType.phone,
                          controller: phoneNumberController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (phoneNumber) {
                            if (phoneNumber == null || phoneNumber == "") {
                              phoneNumberValid = false;
                              return " *B???t bu???c";
                            } else {
                              phoneNumberValid = true;
                              return null;
                            }
                          },
                          decoration: InputDecoration(
                            enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Colors.blue))
                          ),
                        ),
                      ),
                      Expanded(
                          flex: 1,
                          child: IconButton(
                            icon: Icon(Icons.qr_code),
                            onPressed: () async {
                              var status = await Permission.manageExternalStorage.status;
                              debugPrint(status.toString());
                              if(status.isDenied||status.isPermanentlyDenied||status.isRestricted){
                                await Permission.manageExternalStorage.request().then((value){
                                  if(value.isDenied){
                                    return;
                                  }
                                });
                              }
                              ByteData qrData = (await (QrPainter(
                                data: widget.customerData.phoneNumber!,
                                errorCorrectionLevel: QrErrorCorrectLevel.M,
                                version: QrVersions.auto,
                                color: Color.fromRGBO(0, 0, 0, 1),
                                emptyColor: Colors.white,
                                gapless: true,
                              ).toImageData(768)))!;
                              var path =
                                  (await getApplicationDocumentsDirectory()).path;
                              final buffer = qrData.buffer;
/*                          File tempFile = await File(path +
                                    "/" +
                                    BkrmService().currentUser.branchName +
                                    "_" +
                                    widget.customerData.phoneNumber +
                                    ".png")
                                .writeAsBytes(buffer.asUint8List(
                                    qrData.offsetInBytes, qrData.lengthInBytes));*/
                              final result = await ImageGallerySaver.saveImage(
                                  buffer.asUint8List(
                                      qrData.offsetInBytes, qrData.lengthInBytes),
                                  name: BkrmService().currentUser!.branchName.replaceAll(" ", "_") +
                                      "_" +
                                      widget.customerData.phoneNumber!
                                      ,
                              quality: 100);
                              debugPrint(result.toString());
                              if(result["isSuccess"]){
                                _scaffoldKey.currentState!.showSnackBar(SnackBar(
                                  content: Text("???? l??u m?? QR v??o ???????ng d???n :\n"+result["filePath"].toString().replaceAll("file:///storage/emulated/0/", "")),
                                  action: SnackBarAction(
                                      label: "???n",
                                      onPressed: _scaffoldKey.currentState!.hideCurrentSnackBar
                                  ),
                                ));
                              }else{
                                _scaffoldKey.currentState!.showSnackBar(SnackBar(
                                  content: Text("L??u m?? QR th???t b???i !"),
                                  action: SnackBarAction(
                                      label: "???n",
                                      onPressed: _scaffoldKey.currentState!.hideCurrentSnackBar
                                  ),
                                ));
                              }

                            },
                          )),
                      Expanded(
                          flex: 1,
                          child: IconButton(
                            icon: Icon(Icons.print),
                            onPressed: () async {
                              showDialog(context: context, builder: (context){
                                return QrCodePrinter(widget.customerData.phoneNumber!);
                              });
                            },
                          )),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "H??? t??n: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          controller: nameController,
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Colors.blue))
                          ),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "?????a ch??? : ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          controller: addressController,
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Colors.blue))
                          ),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Email : ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          controller: emailController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (email){
                            if(email==null||email==""){
                              emailValid=true;
                              return null;
                            }
                            if (RegExp(
                                r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
                                .hasMatch(email)) {
                              emailValid = true;
                              return null;
                            }else{
                              emailValid = false;
                              return " * Email kh??ng h???p l???";
                            }
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Colors.blue))
                          ),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "??i???m t??ch lu??? : ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: Text(
                          widget.customerData.customerPoint.toString() + " ??i???m",
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.w500),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Text(
                        "Gi???i t??nh: ",
                        style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                      ),
                      Radio(
                        toggleable: editEnable,
                          activeColor: editEnable?Colors.blue:Colors.grey,
                          value: "male",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            if (editEnable) {
                              genderValue = value;
                              genderValid = true;
                              setState(() {});
                            }
                          }),
                      Text(
                        "Nam",
                        style:
                            TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      Radio(
                        toggleable: editEnable,
                          activeColor: editEnable?Colors.blue:Colors.grey,
                          value: "female",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            if (editEnable) {
                              genderValue = value;
                              genderValid = true;
                              setState(() {});
                            }
                          }),
                      Text(
                        "N???",
                        style:
                            TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Ng??y sinh: ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: InkWell(
                          onTap: () {
                            DatePicker.showDatePicker(context,
                                locale: LocaleType.vi,
                                currentTime: DateTime.now(),
                                maxTime: DateTime.now(), onConfirm: (date) {
                                  dateOfBirth = date;
                                  dateOfBirthController.text =
                                      DateFormat("dd-MM-yyyy").format(date);
                                  setState(() {});
                                });
                          },
                          child: IgnorePointer(
                            child: TextFormField(
                              readOnly: true,
                              enabled: editEnable,
                              controller: dateOfBirthController,
                              decoration: InputDecoration(
                                  enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Colors.blue))
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                      child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: editEnable?[
                            ElevatedButton(onPressed: (){
                              clearPage();
                              editEnable=false;
                              setUpCustomerInfo();
                              setState(() {

                              });
                            },child: Container(padding: EdgeInsets.all(10.0),child: Text("H???y"),),),
                            RaisedButton(
                              padding: EdgeInsets.all(10.0),
                              color: Colors.blue,
                              onPressed: () async {
                                if (editEnable) {
                                  debugPrint("phoneNumberValid");
                                  debugPrint(phoneNumberValid.toString());
                                  debugPrint("fullNameValid");
                                  debugPrint(nameValid.toString());
                                  debugPrint("emailValid");
                                  debugPrint(emailValid.toString());
                                  debugPrint("genderValid");
                                  debugPrint(genderValid.toString());
                                  _formKey.currentState!.validate();
                                  if (phoneNumberValid&&emailValid) {
                                    showDialog(
                                        context: context,
                                        builder: (context) {
                                          return AlertDialog(
                                            content: Container(
                                              height: 60,
                                              child: Center(
                                                child: CircularProgressIndicator(),
                                              ),
                                            ),
                                          );
                                        });
                                    MsgInfoCode? returnCode = await BkrmService()
                                        .editCustomer(
                                        id: widget.customerData.id,
                                        phoneNumber:
                                        phoneNumberController.value.text,
                                        name: nameController.value.text,
                                        address:
                                        addressController.value.text == ""
                                            ? null
                                            : addressController.value.text,
                                        email: emailController.value.text == ""
                                            ? null
                                            : emailController.value.text,
                                        gender: genderValue,
                                        dateOfBirth: dateOfBirth,
                                        deleted: false);
                                    Navigator.pop(context);
                                    if (returnCode == MsgInfoCode.actionSuccess) {
                                      editEnable = false;
                                      needRefresh=true;
                                      widget.customerData.name=nameController.value.text;
                                      widget.customerData.gender=genderValue;
                                      widget.customerData.email=emailController.value.text;
                                      widget.customerData.phoneNumber=phoneNumberController.value.text;
                                      widget.customerData.address=addressController.value.text;
                                      widget.customerData.dateOfBirth=dateOfBirth;
                                      showDialog(
                                          context: this.context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text(
                                                  "Thay ?????i th??ng tin kh??ch h??ng th??nh c??ng"),
                                              actions: [
                                                FlatButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                      this.remoteSetState();
                                                    },
                                                    child: Text("????ng"))
                                              ],
                                            );
                                          });
                                      return;
                                    } else {
                                      if (returnCode ==
                                          MsgInfoCode.phoneNumberAlreadyBeenTaken) {
                                        showDialog(
                                            context: this.context,
                                            builder: (context) {
                                              return AlertDialog(
                                                title: Text(
                                                    "S??? ??i???n tho???i ???? t???n t???i"),
                                                actions: [
                                                  FlatButton(
                                                      onPressed: () {
                                                        Navigator.pop(context);
                                                      },
                                                      child: Text("????ng"))
                                                ],
                                              );
                                            });
                                        return;
                                      }
                                      if (returnCode == MsgInfoCode.actionFail) {
                                        showDialog(
                                            context: this.context,
                                            builder: (context) {
                                              return AlertDialog(
                                                title: Text(
                                                    "Thay ?????i th??ng tin kh??ch h??ng th???t b???i"),
                                                actions: [
                                                  FlatButton(
                                                      onPressed: () {
                                                        Navigator.pop(context);
                                                      },
                                                      child: Text("????ng"))
                                                ],
                                              );
                                            });
                                        return;
                                      }
                                      showDialog(
                                          context: this.context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text("L???i h??? th???ng"),
                                              actions: [
                                                FlatButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                    },
                                                    child: Text("????ng"))
                                              ],
                                            );
                                          });
                                    }
                                  }
                                } else {
                                  editEnable = true;
                                  setState(() {});
                                }
                              },
                              child: Text(
                                editEnable == false
                                    ? "Ch???nh s???a"
                                    : "X??c nh???n s???a",
                                style: TextStyle(color: Colors.white, fontSize: 16),
                              ),
                            ),
                            RaisedButton(
                              padding: EdgeInsets.all(10.0),
                              color: Colors.red,
                              child: Text(
                                "Xo?? ",
                                style: TextStyle(color: Colors.white, fontSize: 16),
                              ),
                              onPressed: () {
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return AlertDialog(
                                        title: Text(
                                            "B???n c?? ch???c ch???n mu???n xo?? kh??ch h??ng n??y?"),
                                        actions: [
                                          FlatButton(
                                              onPressed: () async {
                                                Navigator.pop(context);
                                                showDialog(
                                                    context: this.context,
                                                    builder: (context) {
                                                      return AlertDialog(
                                                        content: Container(
                                                          height: 60,
                                                          child: Center(
                                                            child:
                                                            CircularProgressIndicator(),
                                                          ),
                                                        ),
                                                      );
                                                    });
                                                MsgInfoCode? returnCode =
                                                await BkrmService().editCustomer(
                                                    id: widget.customerData.id,
                                                    phoneNumber:
                                                    phoneNumberController
                                                        .value.text,
                                                    name:
                                                    nameController.value.text,
                                                    address: addressController
                                                        .value.text ==
                                                        ""
                                                        ? null
                                                        : addressController
                                                        .value.text,
                                                    email: emailController
                                                        .value.text ==
                                                        ""
                                                        ? null
                                                        : emailController
                                                        .value.text,
                                                    gender: genderValue,
                                                    deleted: true);
                                                if (returnCode ==
                                                    MsgInfoCode.actionSuccess) {
                                                  showDialog(
                                                      context: this.context,
                                                      builder: (context) {
                                                        return AlertDialog(
                                                          title: Text(
                                                              "Xo?? kh??ch h??ng th??nh c??ng"),
                                                          actions: [
                                                            FlatButton(
                                                                onPressed: () {
                                                                  needRefresh=true;
                                                                  Navigator.pop(
                                                                      context);
                                                                  Navigator.pop(
                                                                      context);
                                                                  Navigator.pop(
                                                                      context,needRefresh);
                                                                },
                                                                child: Text("????ng"))
                                                          ],
                                                        );
                                                      });
                                                } else {
                                                  showDialog(context: context, builder: (context){
                                                    return AlertDialog(
                                                      title: Text(
                                                          "Xo?? kh??ch h??ng th???t b???i"),
                                                      actions: [
                                                        FlatButton(
                                                            onPressed: () {
                                                              Navigator.pop(context);
                                                              Navigator.pop(context);
                                                            },
                                                            child: Text("????ng"))
                                                      ],
                                                    );
                                                  });

                                                }
                                              },
                                              child: Text("Ch???c ch???n")),
                                          FlatButton(
                                              onPressed: () {
                                                Navigator.pop(context);
                                              }, child: Text("Hu???")),
                                        ],
                                      );
                                    });
                              },
                            ),
                          ]:
                          [
                        RaisedButton(
                          padding: EdgeInsets.all(10.0),
                          color: Colors.blue,
                          onPressed: () async {
                            if (editEnable) {
                              debugPrint("phoneNumberValid");
                              debugPrint(phoneNumberValid.toString());
                              debugPrint("fullNameValid");
                              debugPrint(nameValid.toString());
                              debugPrint("emailValid");
                              debugPrint(emailValid.toString());
                              debugPrint("genderValid");
                              debugPrint(genderValid.toString());
                              _formKey.currentState!.validate();
                              if (phoneNumberValid&&emailValid) {
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return AlertDialog(
                                        content: Container(
                                          height: 60,
                                          child: Center(
                                            child: CircularProgressIndicator(),
                                          ),
                                        ),
                                      );
                                    });
                                MsgInfoCode? returnCode = await BkrmService()
                                    .editCustomer(
                                        id: widget.customerData.id,
                                        phoneNumber:
                                            phoneNumberController.value.text,
                                        name: nameController.value.text,
                                        address:
                                            addressController.value.text == ""
                                                ? null
                                                : addressController.value.text,
                                        email: emailController.value.text == ""
                                            ? null
                                            : emailController.value.text,
                                        gender: genderValue,
                                        dateOfBirth: dateOfBirth,
                                        deleted: false);
                                Navigator.pop(context);
                                if (returnCode == MsgInfoCode.actionSuccess) {
                                  showDialog(
                                      context: this.context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Text(
                                              "Thay ?????i th??ng tin kh??ch h??ng th??nh c??ng"),
                                          actions: [
                                            FlatButton(
                                                onPressed: () {
                                                  Navigator.pop(context);
                                                  editEnable = false;
                                                  needRefresh=true;
                                                  this.remoteSetState();
                                                },
                                                child: Text("????ng"))
                                          ],
                                        );
                                      });
                                  return;
                                } else {
                                  if (returnCode ==
                                      MsgInfoCode.phoneNumberAlreadyBeenTaken) {
                                    showDialog(
                                        context: this.context,
                                        builder: (context) {
                                          return AlertDialog(
                                            title: Text(
                                                "S??? ??i???n tho???i ???? t???n t???i."),
                                            actions: [
                                              FlatButton(
                                                  onPressed: () {
                                                    Navigator.pop(context);
                                                  },
                                                  child: Text("????ng"))
                                            ],
                                          );
                                        });
                                    return;
                                  }
                                  if (returnCode == MsgInfoCode.actionFail) {
                                    showDialog(
                                        context: this.context,
                                        builder: (context) {
                                          return AlertDialog(
                                            title: Text(
                                                "Thay ?????i th??ng tin kh??ch h??ng th???t b???i"),
                                            actions: [
                                              FlatButton(
                                                  onPressed: () {
                                                    Navigator.pop(context);
                                                  },
                                                  child: Text("????ng"))
                                            ],
                                          );
                                        });
                                    return;
                                  }
                                  showDialog(
                                      context: this.context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Text("L???i h??? th???ng"),
                                          actions: [
                                            FlatButton(
                                                onPressed: () {
                                                  Navigator.pop(context);
                                                },
                                                child: Text("????ng"))
                                          ],
                                        );
                                      });
                                }
                              }
                            } else {
                              editEnable = true;
                              setState(() {});
                            }
                          },
                          child: Text(
                            editEnable == false
                                ? "Ch???nh s???a"
                                : "X??c nh???n s???a",
                            style: TextStyle(color: Colors.white, fontSize: 16),
                          ),
                        ),
                        RaisedButton(
                          padding: EdgeInsets.all(10.0),
                          color: Colors.red,
                          child: Text(
                            "Xo?? ",
                            style: TextStyle(color: Colors.white, fontSize: 16),
                          ),
                          onPressed: () {
                            showDialog(
                                context: context,
                                builder: (context) {
                                  return AlertDialog(
                                    title: Text(
                                        "B???n c?? ch???c ch???n mu???n xo?? kh??ch h??ng n??y?"),
                                    actions: [
                                      FlatButton(
                                          onPressed: () async {
                                            Navigator.pop(context);
                                            showDialog(
                                                context: this.context,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    content: Container(
                                                      height: 60,
                                                      child: Center(
                                                        child:
                                                            CircularProgressIndicator(),
                                                      ),
                                                    ),
                                                  );
                                                });
                                            MsgInfoCode? returnCode =
                                                await BkrmService().editCustomer(
                                                    id: widget.customerData.id,
                                                    phoneNumber:
                                                        phoneNumberController
                                                            .value.text,
                                                    name:
                                                        nameController.value.text,
                                                    address: addressController
                                                                .value.text ==
                                                            ""
                                                        ? null
                                                        : addressController
                                                            .value.text,
                                                    email: emailController
                                                                .value.text ==
                                                            ""
                                                        ? null
                                                        : emailController
                                                            .value.text,
                                                    gender: genderValue,
                                                    deleted: true);
                                            if (returnCode ==
                                                MsgInfoCode.actionSuccess) {
                                              showDialog(
                                                  context: this.context,
                                                  builder: (context) {
                                                    return AlertDialog(
                                                      title: Text(
                                                          "Xo?? kh??ch h??ng th??nh c??ng"),
                                                      actions: [
                                                        FlatButton(
                                                            onPressed: () {
                                                              needRefresh=true;
                                                              Navigator.pop(
                                                                  context);
                                                              Navigator.pop(
                                                                  context);
                                                              Navigator.pop(
                                                                  context,needRefresh);
                                                            },
                                                            child: Text("????ng"))
                                                      ],
                                                    );
                                                  });
                                            } else {
                                              showDialog(context: context, builder: (context){
                                                return AlertDialog(
                                                  title: Text(
                                                      "Xo?? kh??ch h??ng th???t b???i"),
                                                  actions: [
                                                    FlatButton(
                                                        onPressed: () {
                                                          Navigator.pop(context);
                                                          Navigator.pop(context);
                                                        },
                                                        child: Text("????ng"))
                                                  ],
                                                );
                                              });

                                            }
                                          },
                                          child: Text("Ch???c ch???n")),
                                      FlatButton(
                                          onPressed: () {
                                            Navigator.pop(context);
                                          }, child: Text("Hu???")),
                                    ],
                                  );
                                });
                          },
                        ),
                      ]))
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
