-------------------- spu_proyecto_ingreso ---------------------------------
CREATE PROCEDURE [dbo].[spu_proyecto_ingreso]	(@ve_operacion varchar(20)
												,@ve_cod_proyecto_ingreso numeric
												,@ve_nom_proyecto_ingreso varchar(100))

AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into proyecto_ingreso
					(nom_proyecto_ingreso)
			values (@ve_nom_proyecto_ingreso)
	end 
	if (@ve_operacion='UPDATE') begin
		update	proyecto_ingreso
		set		nom_proyecto_ingreso		= @ve_nom_proyecto_ingreso
		where	cod_proyecto_ingreso		= @ve_cod_proyecto_ingreso
	end
END
go