-------------------- spu_centro_costo ----------------------------------
CREATE PROCEDURE [dbo].[spu_centro_costo]
(@ve_operacion varchar(20)
,@ve_cod_centro_costo varchar(30)
,@ve_nom_centro_costo varchar(100)
,@ve_cod_cuenta_contable_ventas numeric = null
,@ve_cod_cuenta_contable_iva numeric = null
,@ve_cod_cuenta_contable_por_cobrar numeric = null)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into centro_costo
					(cod_centro_costo
					,nom_centro_costo
					,cod_cuenta_contable_ventas
					,cod_cuenta_contable_iva
					,cod_cuenta_contable_por_cobrar)
			values (@ve_cod_centro_costo
					,@ve_nom_centro_costo
					,@ve_cod_cuenta_contable_ventas
					,@ve_cod_cuenta_contable_iva
					,@ve_cod_cuenta_contable_por_cobrar)
	end 
	if (@ve_operacion='UPDATE') begin
		update	centro_costo
		set		nom_centro_costo	= @ve_nom_centro_costo
				,cod_cuenta_contable_ventas	= @ve_cod_cuenta_contable_ventas
				,cod_cuenta_contable_iva	= @ve_cod_cuenta_contable_iva
				,cod_cuenta_contable_por_cobrar	= @ve_cod_cuenta_contable_por_cobrar
		where	cod_centro_costo	= @ve_cod_centro_costo
	end
END
go