------------------  spu_doc_nota_venta  --------------------------
CREATE PROCEDURE [dbo].[spu_doc_nota_venta]
			(@ve_operacion varchar(20),
			@cod_doc_nota_venta numeric,
			@cod_nota_venta numeric=NULL,
			@cod_tipo_doc_pago numeric=NULL, 
			@fecha_doc varchar(10)=NULL,
			@nro_doc numeric=NULL, 
			@cod_banco numeric=NULL, 
			@cod_plaza numeric=NULL, 
			@monto_doc numeric=NULL,
			@nro_autoriza varchar(20)=NULL)
AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin						
				update doc_nota_venta
				set  	cod_nota_venta = @cod_nota_venta,
						cod_tipo_doc_pago = @cod_tipo_doc_pago, 
						fecha_doc = dbo.to_date(@fecha_doc),
						nro_doc = @nro_doc, 
						cod_banco = @cod_banco, 
						cod_plaza = @cod_plaza, 
						monto_doc = @monto_doc,
						nro_autoriza = @nro_autoriza
				where 	cod_doc_nota_venta = @cod_doc_nota_venta
			end 
		else if (@ve_operacion='INSERT') 
			begin
				insert into doc_nota_venta (
					cod_nota_venta,
					cod_tipo_doc_pago, 
					fecha_registro, 
					fecha_doc,
					nro_doc, 
					cod_banco, 
					cod_plaza, 
					monto_doc,
					nro_autoriza)
				values (@cod_nota_venta, 
					@cod_tipo_doc_pago, 
					getdate(), 
					dbo.to_date(@fecha_doc), 
					@nro_doc, 
					@cod_banco,
					@cod_plaza,
					@monto_doc,
					@nro_autoriza)
		end 
		else if (@ve_operacion='DELETE') 
			delete doc_nota_venta
			where 	cod_doc_nota_venta = @cod_doc_nota_venta
END
go