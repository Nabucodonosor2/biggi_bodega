-------------------- spu_proyecto_compra ---------------------------------
CREATE PROCEDURE [dbo].[spu_proyecto_compra]
(@ve_operacion varchar(20)
,@ve_cod_cuenta_compra numeric
,@ve_nom_cuenta_compra varchar(100)
,@ve_cod_cuenta_contable_compra numeric
,@ve_cod_cuenta_contable_iva numeric
,@ve_cod_cuenta_contable_por_pagar numeric
,@ve_cod_centro_costo varchar(30))
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into cuenta_compra
					(nom_cuenta_compra
					,cod_cuenta_contable_compra
					,cod_cuenta_contable_iva
					,cod_cuenta_contable_por_pagar
					,cod_centro_costo)
			values (@ve_nom_cuenta_compra
					,@ve_cod_cuenta_contable_compra
					,@ve_cod_cuenta_contable_iva
					,@ve_cod_cuenta_contable_por_pagar
					,@ve_cod_centro_costo)
	end 
	if (@ve_operacion='UPDATE') begin
		update	cuenta_compra
		set		nom_cuenta_compra		= @ve_nom_cuenta_compra
				,cod_cuenta_contable_compra	= @ve_cod_cuenta_contable_compra
				,cod_cuenta_contable_iva	= @ve_cod_cuenta_contable_iva
				,cod_cuenta_contable_por_pagar	= @ve_cod_cuenta_contable_por_pagar
				,cod_centro_costo		= @ve_cod_centro_costo
		where	cod_cuenta_compra		= @ve_cod_cuenta_compra
	end
END
go